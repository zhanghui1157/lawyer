var AreaTool = {
	init : function(data,call_change){
		if(data){
			this.province = data.province || '宁夏';
			this.city = data.city || '银川市';
			this.area = data.area || '金凤区';
		}
		if(call_change){
			this.call_change=call_change;	
		}
		this.get_provinces();
	},
	get_provinces : function(){
		$.ajax({
			type: "get",
			url: '/Public/hplus/js/queryAllProvinces.js',
			async: false,
			dataType: "json",
			success: function(data) {
//				try {
//					data = (new Function('return ' + data))();
//				}catch(e){	
//					console.log(e);
//				}
				var provinces = data.provinces;
				
				var _htl = [];
				for(var i = 0, itm; itm = provinces[i]; i ++){
					_htl.push('<option value="' + itm.id + '"' + (AreaTool.province == itm.provinceName ? ' selected' : '') + '>' + itm.provinceName + '</option>');
				}
				$('#provinces').html(_htl.join(''));
				
				AreaTool.get_cities();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				alert("获取全部省份错误！");
			}
		});
	},
	get_cities : function(){
		$.ajax({
			type: "get",
			url: '/Public/hplus/js/queryCities.js',
			async: false,
			dataType: "json",
			success: function(data) {
//				try {
//					data = (new Function('return ' + data))();
//				}catch(e){		}
				
				AreaTool.cities = data.cities;
				AreaTool.get_areas();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				alert("获取全部城市错误！");
			}
		});
	},
	get_areas : function(){
		var self=this;
		$.ajax({
			type: "get",
			url: '/Public/hplus/js/queryAllAreas.js',
			async: false,
			dataType: "json",
			success: function(data) {
//				try {
//					data = (new Function('return ' + data))();
//				}catch(e){		}
				
				AreaTool.areas = data.areas;
				AreaTool.bind();
				self.call_change();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				alert("获取区域错误！");
			}
		});
	},
	bind : function(){
		var self = this;	
		
		
		$('#provinces').change(function(){
			AreaTool.province = $('#provinces option').eq($('#provinces')[0].selectedIndex).text();
			
			var province_id = $(this).val();
			var _htl = [];
			for(var i = 0, itm; itm = AreaTool.cities[i]; i ++){
				if(province_id == itm.provinceId){
					_htl.push('<option value="' + itm.id + '"' + (AreaTool.city == itm.name ? ' selected' : '') + '>' + itm.name + '</option>');
				}
			}
			$('#cities').html(_htl.join(''));
			
			$('#cities').trigger('change');
		});
		
		$('#cities').change(function(){
			AreaTool.city = $('#cities option').eq($('#cities')[0].selectedIndex).text();
			
			var city_id = $(this).val();
			var _htl = [];
			for(var i = 0, itm; itm = AreaTool.areas[i]; i ++){
				if(city_id == itm.cityId){
					_htl.push('<option value="' + itm.id + '"' + (AreaTool.area == itm.areaName ? ' selected' : '') + '>' + itm.areaName + '</option>');
				}
			}
			$('#areas').html(_htl.join(''));
			$('#areas').trigger('change');
		});
		
		$('#areas').change(function(){
			AreaTool.area = $('#areas option').eq($('#areas')[0].selectedIndex).text();			
			if(AreaTool.call_change){
				AreaTool.call_change();	
			}
			//$('#nearby_box').hide();
		});
		
		$('#provinces').trigger('change');
		
	}
};

