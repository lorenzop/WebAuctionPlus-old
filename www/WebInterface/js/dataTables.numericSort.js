jQuery.fn.dataTableExt.oSort['formatted-num-asc'] = function(x,y){
 x = x.replace(/[^\d\-\.\/]/g,'');
 y = y.replace(/[^\d\-\.\/]/g,'');
 if(x.indexOf('/')>=0)x = eval(x);
 if(y.indexOf('/')>=0)y = eval(y);
 return x/1 - y/1;
}
jQuery.fn.dataTableExt.oSort['formatted-num-desc'] = function(x,y){
 x = x.replace(/[^\d\-\.\/]/g,'');
 y = y.replace(/[^\d\-\.\/]/g,'');
 if(x.indexOf('/')>=0)x = eval(x);
 if(y.indexOf('/')>=0)y = eval(y);
 return y/1 - x/1;
}
jQuery.fn.dataTableExt.oSort['natural-asc']  = function(a,b) {
	return naturalSort(a,b);
};

jQuery.fn.dataTableExt.oSort['natural-desc'] = function(a,b) {
	return naturalSort(a,b) * -1;
};