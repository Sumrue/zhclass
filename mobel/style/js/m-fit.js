function IsPC(){

  var userAgentInfo =  navigator.userAgent;
  var reg = new RegExp("(Android|iPhone|SymbianOS|Windows Phone|iPad|iPod)","ig");
  var isPC =  !reg.test(userAgentInfo);
  return isPC
}
var initFontSize=function(){
  var n=document.getElementsByTagName("html")[0],
      e=document.documentElement.clientWidth;
  if(IsPC()){
      if(e>750){
          n.style.fontSize = "100px"
      }else{
          n.style.fontSize = e/750*100+"px"
      }
  }else{
      n.style.fontSize=e/750*100+"px";
  }
};
initFontSize();
window.οnresize=function(){
  initFontSize()
};