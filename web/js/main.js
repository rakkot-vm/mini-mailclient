function setParams(){
    var ids = $('#grid').yiiGridView('getSelectedRows');
    if(ids != '') {
        $('#btn-multi-del').attr('data-params', JSON.stringify({ids}));
    } else {
        $('#btn-multi-del').removeAttr('data-params');
    }
};