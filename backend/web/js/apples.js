$(document).ready(function() {
    $(document).on('mouseenter', '.apple-action-btn, .apple-eat-input', function() {
        const  title = $(this).attr('title');

        if (title) {
            $(this).attr('data-original-title', title).tooltip('show');
        }
    });

    // Валидация формы съедания
    $('.apple-eat-input').on('input', function() {
        const max = parseInt($(this).attr('max'));
        const value = parseInt($(this).val());

        if (value > max) {
            $(this).val(max);
        }

        if (value < 1) {
            $(this).val(1);
        }
    });
});