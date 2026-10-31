@extends('website.user.layouts.account')
@section('title')تخصصاتي@endsection
@section('account_title')تخصصاتي@endsection
@section('account')
    <form method="post" action="{{ route('specializations.update') }}">
        @csrf
        <div class="rf-acc-card">
            <h3>تخصصاتي</h3>
            <p class="rf-acc-empty mt-0">اختر الخدمات التي تعمل بها، وستظهر لك طلبات العملاء في هذه الخدمات فقط.</p>

            @forelse ($categories as $category)
                <div class="specialization-group">
                    <h5>{{ $category->name }}</h5>
                    <div class="specialization-items">
                        @foreach ($category->subCategories as $subCategory)
                            <input type="checkbox" class="btn-check" name="specializations[]"
                                id="spec_{{ $subCategory->id }}" value="{{ $subCategory->id }}"
                                @checked(in_array($subCategory->id, old('specializations', $selected)))>
                            <label class="specialization-chip" for="spec_{{ $subCategory->id }}">{{ $subCategory->name }}</label>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="rf-acc-empty">لا توجد خدمات متاحة حاليًا.</p>
            @endforelse
        </div>

        @if ($categories->isNotEmpty())
            <button type="submit" class="btn btn-save btn-primary"><i class="bi bi-check2" aria-hidden="true"></i> حفظ التخصصات</button>
        @endif
    </form>
@endsection
