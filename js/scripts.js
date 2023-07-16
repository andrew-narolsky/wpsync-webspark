(function($) {

    $(document).ready(function () {
        $('#start_synchronize').on('submit', function (e) {

            $('.import_samples .error-message').css('display', 'none').text('');
            $('.import_samples .success-message').css('display', 'none').text('');

            e.preventDefault();
            let _this = $(this);
            let url = _this.find('input[name=admin-url]').val();
            let data = _this.serialize();
            _this.find('button').attr('disabled', '');

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: function (res) {
                    $('.import_samples .success-message').css('display', 'block').text('Synchronize are started!');
                    _this.find('button').removeAttr('disabled');
                },
                error: function () {
                    $('.import_samples .error-message').css('display', 'block').text('Error! Try later');
                    _this.find('button').removeAttr('disabled');
                }
            });
        });
    });
})(jQuery);