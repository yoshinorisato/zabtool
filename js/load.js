
function LoadingMsg(){
    if(self.attachEvent || self.addEventListener){ var wimg = new Image();
        /*上からの表示位置 % かpxで */
        var PTop = '35%';

        /*表示する文字列 */
        var msgs ='Now Loading......';

        /*表示する『ロード中』画像 */
        wimg.src = './wait30.gif';

        /* 細かいCSS設定の調整は↓の１行。タグ打出し部分を調整 */
        document.write('<div id="Loadouter" style="top:',PTop,';position: absolute; width:95%; z-index: 100; color:#9999cc; text-align:center;"><table id="Loadinner" style="margin:auto; border:1px solid #aaaaaa; font-size: 13px; text-align:left;"><tr><td>',msgs,'</td></tr><tr><td><img src="',wimg.src,'" border=0></td></tr></table></div>');
        function by(id){ if(document.getElementById){ return document.getElementById(id).style; }; if(document.all){ return document.all(id).style ; }}
        function addEv(obj, type, func){ if(obj.addEventListener){ obj.addEventListener(type, func, false); }else{ if(obj.attachEvent) obj.attachEvent('on' + type, func); }}
        addEv(window, 'load', function(){by('Loadouter').display = 'none';});
    }
};LoadingMsg();
