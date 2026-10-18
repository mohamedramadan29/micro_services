<?php

namespace Database\Seeders;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\RequestOffer;
use App\Models\RequestPayment;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\ServiceRequests\RequestSubmission;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data for the service request system (staging/local only):
 *   php artisan db:seed --class=ServiceRequestsDemoSeeder
 *
 * Accounts (password: password): demo-client@nafizha.test,
 * demo-freelancer1@nafizha.test, demo-freelancer2@nafizha.test.
 * It covers every state: open with offers and a conversation, open with no
 * offer for over an hour (for the alert), a price inquiry with a team message,
 * a completed (paid out) request and a closed one.
 */
class ServiceRequestsDemoSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Demo data is not seeded in production.');

            return;
        }

        [$ads, $paidAds, $identity] = $this->services();
        $form = $this->form($ads);
        $this->copyForm($form, $paidAds);

        $client = $this->user('demo-client@nafizha.test', 'سارة (عميلة تجريبية)', User::TYPE_CLIENT, ['phone' => '01000000001']);
        $freelancer1 = $this->user('demo-freelancer1@nafizha.test', 'أحمد (مستقل تجريبي)', User::TYPE_FREELANCER, ['job_title' => 'خبير إعلانات ممولة']);
        $freelancer2 = $this->user('demo-freelancer2@nafizha.test', 'منى (مستقلة تجريبية)', User::TYPE_FREELANCER, ['job_title' => 'مسوّقة رقمية']);
        // Demo freelancers are approved with a checked WhatsApp so they can send offers (no photo: not listed).
        foreach ([$freelancer1, $freelancer2] as $demoFreelancer) {
            $demoFreelancer->forceFill(['whatsapp' => '+201000000000', 'email_verified_at' => now(), 'freelancer_status' => User::FREELANCER_APPROVED])->save();
        }
        $freelancer1->specializations()->syncWithoutDetaching([$ads->id, $paidAds->id]);
        $freelancer2->specializations()->syncWithoutDetaching([$ads->id, $identity->id]);

        if (ServiceRequest::where('user_id', $client->id)->exists()) {
            $this->command?->info('Demo requests already exist, skipped.');

            return;
        }

        // 1) Open request with two offers and a conversation.
        $open = $this->submit($client, $form, 'execute', 'أريد زيادة مبيعات متجري الإلكتروني للعناية بالبشرة خلال الشهرين القادمين.');
        $offer1 = $this->offer($open, $freelancer1, 450, 6, 'حملة على فيسبوك وإنستغرام لمدة شهر، 6 تصاميم وتقارير أسبوعية.');
        $this->offer($open, $freelancer2, 380, 8, 'حملة على إنستغرام وتيك توك مع 4 تصاميم وتقرير نهائي.');
        $offer1->messages()->create(['service_request_id' => $open->id, 'sender_id' => $client->id, 'body' => 'هل يشمل العرض كتابة النص الإعلاني؟']);
        $offer1->messages()->create(['service_request_id' => $open->id, 'sender_id' => $freelancer1->id, 'body' => 'نعم، نصين لكل إعلان وتعديل واحد.']);
        $offer1->update(['status' => RequestOffer::STATUS_IN_DISCUSSION]);

        // 2) Published over an hour ago with no offers: `php artisan requests:alert-no-offers` alerts on it.
        $quiet = $this->submit($client, $form, 'execute', 'حملة إعلانية لافتتاح مطعم جديد في الرياض.');
        $quiet->update(['published_at' => now()->subMinutes(75)]);

        // 3) Private price inquiry with a question from the team.
        $inquiry = $this->submit($client, $form, 'explore', 'أريد معرفة تكلفة حملة شهرية قبل أن أقرر.');
        $inquiry->adminMessages()->create(['sender_id' => User::activeAdmins()->value('id'), 'from_admin' => true, 'body' => 'تبدأ الحملة الشهرية من 3000 ج.م. هل تفضل فيسبوك أم إنستغرام؟']);

        // 4) Completed: paid, delivered, confirmed and paid out.
        $done = $this->submit($client, $form, 'execute', 'حملة إطلاق منتج جديد (منفّذة).');
        $doneOffer = $this->offer($done, $freelancer1, 300, 5, 'حملة إطلاق لمدة أسبوعين.');
        $this->complete($done, $doneOffer);

        // 5) Closed without being carried out.
        $closed = $this->submit($client, $form, 'execute', 'حملة موسمية ألغيتها.');
        $closed->update(['status' => ServiceRequest::STATUS_NOT_COMPLETED, 'closed_reason' => 'client_cancelled', 'closed_at' => now(), 'closed_by' => $client->id]);

        $this->command?->info('Demo requests: ' . implode(', ', [$open->number, $quiet->number, $inquiry->number, $done->number, $closed->number]));
    }

    private function services(): array
    {
        $marketing = Category::firstOrCreate(['name' => 'تسويق وإعلان'], ['slug' => 'demo-marketing', 'image' => 'demo.png', 'status' => 1]);
        $design = Category::firstOrCreate(['name' => 'تصميم'], ['slug' => 'demo-design', 'image' => 'demo.png', 'status' => 1]);
        $sub = fn (Category $category, string $name, string $slug) => SubCategory::firstOrCreate(
            ['name' => $name], ['slug' => $slug, 'image' => 'demo.png', 'parent_id' => $category->id, 'status' => 1]
        );

        return [$sub($marketing, 'إعلانات المواقع', 'demo-site-ads'), $sub($marketing, 'إعلانات ممولة', 'demo-paid-ads'), $sub($design, 'تصميم هوية', 'demo-identity')];
    }

    /** The example form from page 2 of the client's spec. */
    private function form(SubCategory $service): ServiceForm
    {
        $existing = ServiceForm::main()->where('sub_category_id', $service->id)->first();
        if ($existing && $existing->questions()->exists()) {
            $existing->update(['status' => ServiceForm::STATUS_PUBLISHED, 'published_at' => $existing->published_at ?? now()]);

            return $existing;
        }

        $form = $existing ?? ServiceForm::create(['sub_category_id' => $service->id]);
        $form->update(['status' => ServiceForm::STATUS_PUBLISHED, 'published_at' => now()]);
        $details = $form->sections()->create(['title' => 'تفاصيل الحملة', 'sort_order' => 1]);
        $budget = $form->sections()->create(['title' => 'الميزانية والتوقيت', 'sort_order' => 2]);

        $order = 0;
        $add = function ($section, array $data) use ($form, &$order) {
            return $form->questions()->create($data + ['service_form_section_id' => $section->id, 'sort_order' => ++$order]);
        };
        $add($details, ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'ما الهدف الرئيسي من الإعلان؟', 'is_required' => true, 'options' => ['زيادة المبيعات', 'جذب عملاء', 'زيادة الزيارات', 'تعريف بالعلامة']]);
        $add($details, ['type' => Q::TYPE_LONG_TEXT, 'label' => 'ما المنتج أو الخدمة التي تريد الترويج لها؟', 'is_required' => true, 'settings' => ['min_length' => null, 'max_length' => 2000]]);
        $hasSite = $add($details, ['type' => Q::TYPE_YES_NO, 'label' => 'هل لديك موقع إلكتروني؟', 'is_required' => true]);
        $add($details, ['type' => Q::TYPE_URL, 'label' => 'ما رابط الموقع؟', 'is_required' => true, 'condition' => ['question_id' => $hasSite->id, 'values' => [Q::YES], 'negate' => false]]);
        $add($details, ['type' => Q::TYPE_MULTI_CHOICE, 'label' => 'أين تريد استهداف الجمهور؟', 'is_required' => true, 'options' => ['مصر', 'السعودية', 'الإمارات', 'الكويت', 'دول أخرى']]);
        $add($details, ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'مكان التنفيذ', 'filter_key' => 'work_mode', 'options' => ['عن بُعد', 'حضوري']]);
        $add($budget, ['type' => Q::TYPE_NUMBER, 'label' => 'ما ميزانيتك التقريبية؟', 'filter_key' => 'budget', 'settings' => ['mode' => 'range', 'min' => 50, 'max' => 100000, 'unit' => 'ج.م']]);
        $add($budget, ['type' => Q::TYPE_DATE, 'label' => 'متى ترغب ببدء الحملة؟', 'filter_key' => 'deadline']);
        $add($budget, ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'ما مدى استعجالك؟', 'filter_key' => 'urgency', 'options' => ['عادي', 'عاجل']]);

        return $form->refresh();
    }

    private function copyForm(ServiceForm $source, SubCategory $target): void
    {
        if (! ServiceForm::main()->where('sub_category_id', $target->id)->exists()) {
            ServiceForm::create(['sub_category_id' => $target->id, 'status' => ServiceForm::STATUS_PUBLISHED, 'published_at' => now()])->copyFrom($source);
        }
    }

    private function user(string $email, string $name, string $type, array $extra = []): User
    {
        return User::firstOrCreate(['email' => $email], $extra + [
            'name' => $name,
            'user_name' => strstr($email, '@', true),
            'password' => Hash::make(self::PASSWORD),
            'account_type' => $type,
            'status' => 1,
        ]);
    }

    /** Submit through the real validation and storage, like the request page does. */
    private function submit(User $client, ServiceForm $form, string $action, string $problem): ServiceRequest
    {
        $answers = [];
        foreach ($form->orderedQuestions() as $question) {
            $answers[$question->id] = match ($question->label) {
                'ما الهدف الرئيسي من الإعلان؟' => 'زيادة المبيعات',
                'ما المنتج أو الخدمة التي تريد الترويج لها؟' => 'منتجات عناية بالبشرة طبيعية',
                'هل لديك موقع إلكتروني؟' => Q::YES,
                'ما رابط الموقع؟' => 'https://shop.example.com',
                'أين تريد استهداف الجمهور؟' => ['مصر', 'السعودية'],
                'مكان التنفيذ' => 'عن بُعد',
                'ما ميزانيتك التقريبية؟' => ['min' => 300, 'max' => 600],
                'متى ترغب ببدء الحملة؟' => now()->addWeeks(3)->toDateString(),
                'ما مدى استعجالك؟' => 'عاجل',
                default => null,
            };
        }

        $request = Request::create('/request', 'POST', [
            'sub_category_id' => $form->sub_category_id,
            'action' => $action,
            'answers' => array_filter($answers, fn ($value) => $value !== null),
            'problem' => $problem,
            'contact_name' => $client->name,
            'contact_phone' => $client->phone ?: '01000000001',
            'contact_email' => $client->email,
        ]);
        $submission = new RequestSubmission($form);

        return $submission->store($client, $submission->validate($request), $request);
    }

    private function offer(ServiceRequest $request, User $freelancer, float $price, int $days, string $scope): RequestOffer
    {
        return $request->offers()->create([
            'freelancer_id' => $freelancer->id,
            'price' => $price,
            'duration_days' => $days,
            'scope' => $scope,
            'message' => 'عرض تجريبي: يمكنني البدء فورًا ولدي أعمال مشابهة.',
            'portfolio_links' => ['https://portfolio.example.com'],
        ]);
    }

    /** A paid, delivered and confirmed request (no real payment provider involved). */
    private function complete(ServiceRequest $request, RequestOffer $offer): void
    {
        $commission = round((float) $offer->price * 0.10, 2);
        $payment = $request->payments()->create([
            'request_offer_id' => $offer->id,
            'client_id' => $request->user_id,
            'freelancer_id' => $offer->freelancer_id,
            'amount' => $offer->price,
            'currency' => 'EGP',
            'commission_rate' => 10,
            'commission_amount' => $commission,
            'freelancer_amount' => (float) $offer->price - $commission,
            'status' => RequestPayment::STATUS_RELEASED,
            'gateway_reference' => 'demo_' . $request->number,
            'paid_at' => now()->subDays(6),
            'released_at' => now()->subDay(),
        ]);
        foreach ([
            [$request->user_id, WalletTransaction::TYPE_ESCROW_IN, $payment->amount, 'دفع قيمة الطلب ' . $request->number . ' (تجريبي)'],
            [$offer->freelancer_id, WalletTransaction::TYPE_RELEASE, $payment->freelancer_amount, 'مستحقات الطلب ' . $request->number . ' (تجريبي)'],
            [null, WalletTransaction::TYPE_COMMISSION, $commission, 'عمولة الطلب ' . $request->number . ' (تجريبي)'],
        ] as [$userId, $type, $amount, $description]) {
            WalletTransaction::create(['user_id' => $userId, 'request_payment_id' => $payment->id, 'type' => $type, 'amount' => $amount, 'description' => $description]);
        }

        $offer->update(['status' => RequestOffer::STATUS_ACCEPTED]);
        $request->update([
            'status' => ServiceRequest::STATUS_COMPLETED,
            'accepted_offer_id' => $offer->id,
            'delivered_at' => now()->subDays(2),
            'delivery_note' => 'تم تسليم الحملة والتقرير النهائي.',
            'completed_at' => now()->subDay(),
        ]);
    }
}
