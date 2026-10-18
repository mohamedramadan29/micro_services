{{--
    A whole request form body: the service's questions, then the shared client
    fields. Expects $form (with sections.questions loaded) and optional $client.
--}}
<div class="rf-form" data-request-form>
    <div data-service-questions>
        @include('website.service-requests.partials.service-questions', ['form' => $form])
    </div>
    @include('website.service-requests.partials.shared-fields', ['client' => $client ?? null])
</div>
