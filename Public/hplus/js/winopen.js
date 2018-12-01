function winopen(obj,x='80%',y='80%'){
    layer.open({
        type: 2,
        title: $(obj).attr('tip'),
        maxmin: true, //开启最大化最小化按钮
        area: [x, y],
        content: $(obj).attr('address')
    });

}
