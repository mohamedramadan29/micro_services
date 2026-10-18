<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion;
use App\Models\ServiceFormSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceFormController extends Controller
{
    /** Every service with the state of its request form. */
    public function index()
    {
        $categories = Category::with(['subCategories' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        $forms = ServiceForm::main()->withCount('questions')->with('revision:id,revision_of_id')->get()->keyBy('sub_category_id');

        return view('admin.request-forms.index', compact('categories', 'forms'));
    }

    /** The builder for one service's form (or the "create form" screen). */
    public function show(SubCategory $subCategory)
    {
        $form = ServiceForm::main()->where('sub_category_id', $subCategory->id)->first();
        if ($form) {
            return $this->builder($form);
        }

        $copySources = ServiceForm::main()->with('subCategory')
            ->withCount('questions')
            ->where('sub_category_id', '!=', $subCategory->id)
            ->get()
            ->filter(fn ($source) => $source->questions_count > 0 && $source->subCategory);

        $questionsById = collect();

        return view('admin.request-forms.show', compact('subCategory', 'form', 'copySources', 'questionsById'));
    }

    /** The builder for a form: a service's main form or its draft revision. */
    public function builder(ServiceForm $form)
    {
        $form->load(['sections.questions', 'subCategory.category', 'revision', 'revisionOf']);
        $subCategory = $form->subCategory;
        $questionsById = $form->sections->flatMap->questions->keyBy('id');
        $copySources = collect();

        return view('admin.request-forms.show', compact('subCategory', 'form', 'copySources', 'questionsById'));
    }

    /** Edit a published form as a draft: clients keep the published one until the changes are published. */
    public function createRevision(ServiceForm $form)
    {
        abort_if($form->isRevision(), 404);

        return redirect()->route('admin.request-forms.builder', $form->createRevision())
            ->with('success', 'هذه مسودة تعديل: عدّل براحتك، العملاء يرون النموذج المنشور حتى تضغط «نشر التعديلات»');
    }

    public function publishRevision(ServiceForm $form)
    {
        try {
            $main = $form->publishRevision();
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('admin.request-forms.builder', $main)->with('success', 'تم نشر التعديلات، وأصبحت ظاهرة للعملاء');
    }

    public function discardRevision(ServiceForm $form)
    {
        abort_unless($form->isRevision(), 404);
        $main = $form->revisionOf;
        $form->delete();

        return redirect()->route('admin.request-forms.builder', $main)->with('success', 'تم تجاهل مسودة التعديل');
    }

    public function store(Request $request, SubCategory $subCategory)
    {
        if (ServiceForm::main()->where('sub_category_id', $subCategory->id)->exists()) {
            return redirect()->route('admin.request-forms.show', $subCategory);
        }

        $data = $request->validate([
            'copy_from' => ['nullable', Rule::exists('service_forms', 'id')],
        ], [
            'copy_from.exists' => 'النموذج المراد النسخ منه غير موجود',
        ]);

        DB::transaction(function () use ($subCategory, $data) {
            $form = ServiceForm::create(['sub_category_id' => $subCategory->id]);

            if (! empty($data['copy_from'])) {
                $form->copyFrom(ServiceForm::findOrFail($data['copy_from']));
            } else {
                $form->sections()->create(['title' => 'تفاصيل المشروع', 'sort_order' => 1]);
            }
        });

        return redirect()->route('admin.request-forms.show', $subCategory)
            ->with('success', 'تم إنشاء نموذج الخدمة كمسودة، أضف الأسئلة ثم انشره');
    }

    public function updateStatus(Request $request, ServiceForm $form)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(ServiceForm::STATUSES))],
        ]);

        if ($form->isRevision()) {
            return back()->withErrors('استخدم «نشر التعديلات» لنشر مسودة التعديل');
        }
        if ($data['status'] === ServiceForm::STATUS_PUBLISHED && ! $form->questions()->exists()) {
            return back()->withErrors('لا يمكن نشر نموذج بدون أسئلة');
        }

        $form->update([
            'status' => $data['status'],
            'published_at' => $data['status'] === ServiceForm::STATUS_PUBLISHED ? ($form->published_at ?? now()) : $form->published_at,
        ]);

        $messages = [
            ServiceForm::STATUS_PUBLISHED => 'تم نشر النموذج، وأصبح متاحًا للعملاء',
            ServiceForm::STATUS_PAUSED => 'تم إيقاف النموذج، ولن يظهر للعملاء',
            ServiceForm::STATUS_DRAFT => 'تم إرجاع النموذج إلى مسودة',
        ];

        return back()->with('success', $messages[$data['status']]);
    }

    /** Drag-and-drop: new order of sections and of the questions inside them. */
    public function reorder(Request $request, ServiceForm $form)
    {
        $data = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|integer',
            'sections.*.questions' => 'present|array',
            'sections.*.questions.*' => 'integer',
        ]);

        $sectionIds = collect($data['sections'])->pluck('id')->map(fn ($id) => (int) $id);
        $questionIds = collect($data['sections'])->pluck('questions')->flatten()->map(fn ($id) => (int) $id);

        $formSectionIds = $form->sections()->pluck('id');
        $formQuestionIds = $form->questions()->pluck('id');

        if ($sectionIds->sort()->values()->all() !== $formSectionIds->sort()->values()->all()
            || $questionIds->sort()->values()->all() !== $formQuestionIds->sort()->values()->all()) {
            return response()->json(['message' => 'ترتيب غير صالح، أعد تحميل الصفحة وحاول مرة أخرى'], 422);
        }

        DB::transaction(function () use ($data) {
            foreach (array_values($data['sections']) as $sectionIndex => $section) {
                ServiceFormSection::whereKey($section['id'])->update(['sort_order' => $sectionIndex + 1]);
                foreach (array_values($section['questions']) as $questionIndex => $questionId) {
                    ServiceFormQuestion::whereKey($questionId)->update([
                        'service_form_section_id' => $section['id'],
                        'sort_order' => $questionIndex + 1,
                    ]);
                }
            }
        });

        return response()->json(['message' => 'تم حفظ الترتيب']);
    }

    /** The form exactly as a client will see it (works for drafts too). */
    public function preview(ServiceForm $form)
    {
        $form->load(['subCategory.category', 'sections.questions']);

        return view('website.service-requests.preview', compact('form'));
    }
}
