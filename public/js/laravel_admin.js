$(document).ready(function () {
    $('.view-description').click(function (e) {
        e.preventDefault();

        var description = $(this).data('description');

        $('#modalDescriptionTitle').text("Full Description");
        $('#modalDescriptionContent').text(description);

        $('#descriptionModal').modal('show');
    });

    $('.view-image').click(function (e) {
        e.preventDefault();
        var imgSrc = $(this).data('img');
        $('#modalImageContent').attr('src', imgSrc);
        $('#imageModal').modal('show');
    });
});