document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.treeview-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('open');
        });
    });
});
