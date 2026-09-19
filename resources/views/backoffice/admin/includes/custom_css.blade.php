<style>
    /* Category create/update modal: keep the footer visible while the body scrolls. */
    #categoryFormModal .modal-dialog {
        width: calc(100% - 2rem);
        max-width: 950px;
        height: calc(100vh - 2rem) !important;
        margin: 1rem auto;
    }

    #categoryFormModal .modal-content {
        height: 100% !important;
        max-height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* #categoryAjaxForm is the direct child of .modal-content. */
    #categoryFormModal .modal-content > form,
    #categoryFormModal #categoryAjaxForm {
        height: 100%;
        min-height: 0;
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
    }

    #categoryFormModal .modal-header,
    #categoryFormModal .modal-footer {
        flex: 0 0 auto;
    }

    #categoryFormModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    #categoryFormModal .modal-footer {
        position: relative;
        z-index: 5;
        background: #f8f9fa !important;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, .06);
    }

    #categoryFormModal .nav-tabs {
        position: sticky;
        top: -1rem;
        z-index: 4;
        background: #ffffff;
        padding-top: .25rem;
    }

    /* Smaller cards only on the Media index/trash listing. */
    .media-card .media-preview {
        height: 140px;
    }

    .media-card .card-body {
        padding: .75rem !important;
    }

    .media-card .card-footer {
        padding: .5rem !important;
    }

    .media-card .badge {
        font-size: 10px;
    }

    @media (max-width: 767.98px) {
        #categoryFormModal .modal-dialog {
            width: calc(100% - 1rem);
            height: calc(100vh - 1rem) !important;
            margin: .5rem auto;
        }

        #categoryFormModal .modal-header,
        #categoryFormModal .modal-body,
        #categoryFormModal .modal-footer {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        #categoryFormModal .nav-tabs {
            overflow-x: auto;
            flex-wrap: nowrap;
            white-space: nowrap;
        }
    }
</style>

<style>
    /* Compact media index cards; no controller or page logic changes. */
    .media-card {
        border-radius: 10px;
    }

    .media-card .media-preview {
        height: 130px !important;
    }

    .media-card .card-body {
        padding: .5rem !important;
    }

    .media-card .card-footer {
        padding: .35rem !important;
    }

    .media-card .card-footer .btn {
        padding: .2rem .45rem;
        font-size: .75rem;
    }
</style>
