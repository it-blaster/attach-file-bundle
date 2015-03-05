
function file_delete(e) {
    e.preventDefault();
    if (confirm('Удалить?')) {
        var url = $(this).attr('href');
        var $file_block = $(this).parents('.file_block');
        var $loader = $file_block.find('.loader');
        $(this).hide();
        $loader.show();
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    $file_block.remove();
                } else {
                    alert('Не удалось удалить файл. Во время выполнения произошли ошибки')
                }
            }
        });
    }
}

$(function(){
    $(document).on('click', '.attach_file_delete', file_delete);
})