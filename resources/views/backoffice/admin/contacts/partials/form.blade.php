<div class="modal fade" id="contactFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="contactFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="contactAjaxForm" action="{{ route('admin.contacts.store') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="_method" id="contactFormMethod" value="POST">

                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold" id="contactFormModalTitle">
                        <i class="fas fa-address-card text-primary mr-2"></i><span>Create Contact</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body p-4">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="contact-name">Name *</label>
                            <input type="text" id="contact-name" name="name" class="form-control" maxlength="150" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="contact-email">Email *</label>
                            <input type="email" id="contact-email" name="email" class="form-control" maxlength="191" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="contact-phone">Phone *</label>
                            <input type="text" id="contact-phone" name="phone" class="form-control" maxlength="50" placeholder="+880 1712-345678" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="contact-status">Status *</label>
                            <select id="contact-status" name="status" class="custom-select" required>
                                @foreach(\App\Models\Contact::STATUSES as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="contact-map-url">Map URL *</label>
                        <input type="text" id="contact-map-url" name="map_url" class="form-control" maxlength="5000" placeholder="Google Maps share URL, embed URL, or copied iframe HTML" required>
                        <small class="form-text text-muted">Paste a Google Maps share URL or, for the most reliable live preview on restricted cPanel hosting, Google Maps → Share → Embed a map → Copy HTML. ShopPilot safely extracts only the iframe src URL; raw iframe/script HTML is never stored.</small>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="btnSubmitContactForm">
                        <i class="fas fa-save mr-1"></i> Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
