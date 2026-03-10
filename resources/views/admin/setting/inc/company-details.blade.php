<div class="tab-pane fade" id="v-pills-company" role="tabpanel">
    <div class="card mb-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold mb-4">{{ _trans('common.Web Branding') }}</h5>
            <form>
                <div class="row g-3">
                    <x-form.image-upload name="company_logo" label="Company Logo" value="{{ @$settings->company_logo }}"
                        required />

                    <x-form.image-upload name="favicon" label="Favicon" value="{{ @$settings->favicon }}" />


                    <x-form.input name="company_name" label="Company Name" placeholder="Enter company name"
                        value="{{ @$settings->company_name }}" required />

                    <x-form.input name="company_email" label="Company Email" type="email"
                        placeholder="Enter company email" value="{{ @$settings->company_email }}" required />

                    <x-form.input name="company_phone" label="Company Phone" type="number"
                        placeholder="Enter company phone" value="{{ @$settings->company_phone }}" />

                    <x-form.input name="company_description" label="Company Description"
                        placeholder="Enter company description" value="{{ @$settings->company_description }}"
                        required />

                    <x-form.textarea name="company_address" label="Company Address" placeholder="Enter company address"
                        value="{{ @$settings->company_address }}" required />

                    <x-ui.button variant="success" block>
                        Submit
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
