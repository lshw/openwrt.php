function modi(url,text,Defaulttext)
{
var data=prompt(text,Defaulttext);
if(data==null)//取消
    return false;
if(data){
if( data != Defaulttext) {
    location.replace(url+data);
} else  {
if(confirm('内容没有修改,提交吗?'))  location.replace(url+data);
else  return false;
}
}else {//输入空
        return false;
    }
}

