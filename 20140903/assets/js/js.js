function goReload(){
	location.reload();
}

$(function () {
	$('#defaultCountdown').countdown({until: "+10m",format: 'MS',onExpiry: goReload,compact: true});
        
        $(".jsDownloadBtn").click(function(e){
            e.preventDefault();
            $.get( "download.php"  ,function(msg){
                alert('download @' + msg);
                location.reload();
            }) ;
        });
});