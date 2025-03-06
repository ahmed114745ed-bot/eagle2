<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
        @if(config('admin.show_environment'))
            <strong>Env</strong>&nbsp;&nbsp; {!! config('app.env') !!}
        @endif

        &nbsp;&nbsp;&nbsp;&nbsp;

        @if(config('admin.show_version'))
        <strong>Version</strong>&nbsp;&nbsp; {!! \Encore\Admin\Admin::VERSION !!}
        @endif

    </div>
    <!-- Default to the left -->
    <div class="modal fade" id="langModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 900px;    min-height: 500px;">
        <div class="modal-content" style="background-color: var(--box-background-color); color: var(--text-primary-color); height:100%;">
            <div class="modal-header" style="background-color: var(--primary-color); color: var(--text-secondary-color);">
                <h5 class="modal-title" id="modalLangTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" style="color: var(--text-secondary-color);">&times;</button>
            </div>
            <div class="modal-body" style="height:80%; background-color: var(--table-background-color); color: var(--text-primary-color);">
                <p id="modalLangContent"></p>
            </div>
            <div class="modal-footer" style="background-color: var(--secondary-color);">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background-color: var(--primary-color); border: none; color: white;">Close</button>
            </div>
        </div>
    </div>
</div>

    <strong>Powered by <a href="https://github.com/z-song/laravel-admin" target="_blank">{{config('admin.company_name')}}</a></strong>
</footer>
