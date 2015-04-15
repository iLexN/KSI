function goReload(){
	location.reload();
}

$(function () {
	$('#defaultCountdown').countdown({until: "+10m",format: 'MS',onExpiry: goReload,compact: true});
        
        $(".jsDownloadBtn").click(function(e){
            e.preventDefault();
            $.get( "download.php?t=m"  ,function(msg){
                alert('download @' + msg);
                location.reload();
            }) ;
        });
        
        $("a.oldrefid").click(function(e){
            e.preventDefault();
            var
                px = Math.floor(((screen.availWidth || 1024) - 500) / 2),
                py = Math.floor(((screen.availHeight || 700) - 500) / 2);
            window.open($(this).attr('href'), "social", "width=600,height=600,left="+px+",top="+py+",location=0,menubar=0,toolbar=0,status=0,scrollbars=1,resizable=1");
        });
        
        
});