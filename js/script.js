P=new Array(4);		G=new Array(4);		
P[1]='/zabtool/importFileSel.php';				G[1]='ホストインポート';
P[2]='/zabtool/selectGroup.php';					G[2]='ホストエクスポート';
P[3]='/zabtool/ALARMRep/selectDateOut.php';		G[3]='アラーム統計リポート';
P[4]='/zabtool/EVENTClose/selectDateOut.php';		G[4]='イベントクローズ';


function WriteMenu(_){
	document.write('<div id="title">  SMARTWATCH   一括設定ツール</div>')
	document.write('<div id="menu_ul2"><ul>')
	for(i=1;i<G.length;i++){
		document.write('<li')
		if(i==_)document.write(' id="current"')
		
		document.write('><a href="'+P[i]+'"><span>')
		document.write(G[i])
		document.write('</span></a></li>')
	}
	document.write('</ul></div>')
	document.write('<div id="menu_under_line">&nbsp;</div>')

}
