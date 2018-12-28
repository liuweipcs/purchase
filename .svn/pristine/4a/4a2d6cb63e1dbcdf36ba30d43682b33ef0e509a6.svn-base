//校验统一社会信用代码
function checkSocialCreditCode(Code)
{
    var patrn = /^[0-9A-Z]+$/;
    //18位校验及大写校验
    if ((Code.length != 18) || (patrn.test(Code) == false)) {
        return false;
    }else{
        var Ancode;//统一社会信用代码的每一个值
        var Ancodevalue;//统一社会信用代码每一个值的权重
        var total = 0;
        var weightedfactors = [1, 3, 9, 27, 19, 26, 16, 17, 20, 29, 25, 13, 8, 24, 10, 30, 28];//加权因子
        var str = '0123456789ABCDEFGHJKLMNPQRTUWXY';
        //不用I、O、S、V、Z
        for (var i = 0; i < Code.length - 1; i++)
        {
            Ancode = Code.substring(i, i + 1);
            Ancodevalue = str.indexOf(Ancode);
            total = total + Ancodevalue * weightedfactors[i];
            //权重与加权因子相乘之和
        }
        var logiccheckcode = 31 - total % 31;

        if (logiccheckcode == 31) {
            logiccheckcode = 0;
        }
        var Str = "0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,J,K,L,M,N,P,Q,R,T,U,W,X,Y";
        var Array_Str = Str.split(',');
        logiccheckcode = Array_Str[logiccheckcode];
        var checkcode = Code.substring(17, 18);
        if (logiccheckcode != checkcode) {
            return false;
        }
        return true;
    }
}

//校验银行卡号
function checkBankNumber(bankno){
    if(bankno.length < 16 || bankno.length > 19) {
        return false;
    }
    var num = /^\d*$/;//全数字
    if(!num.exec(bankno)) {
        return false;
    }
    //开头6位
    var strBin = "10,18,30,35,37,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,58,60,62,65,68,69,84,87,88,94,95,98,99";
    if(strBin.indexOf(bankno.substring(0, 2)) == -1) {
        return false;
    }
    var lastNum=bankno.substr(bankno.length-1,1);//取出最后一位（与luhn进行比较）
    var first15Num=bankno.substr(0,bankno.length-1);//前15或18位
    var newArr=new Array();
    for(var i=first15Num.length-1;i>-1;i--){    //前15或18位倒序存进数组
        newArr.push(first15Num.substr(i,1));
    }
    var arrJiShu=new Array();  //奇数位*2的积 <9
    var arrJiShu2=new Array(); //奇数位*2的积 >9
    var arrOuShu=new Array();  //偶数位数组
    for(var j=0;j<newArr.length;j++){
        if((j+1)%2==1){//奇数位
            if(parseInt(newArr[j])*2<9)
                arrJiShu.push(parseInt(newArr[j])*2);
            else
                arrJiShu2.push(parseInt(newArr[j])*2);
        }
        else //偶数位
            arrOuShu.push(newArr[j]);
    }

    var jishu_child1=new Array();//奇数位*2 >9 的分割之后的数组个位数
    var jishu_child2=new Array();//奇数位*2 >9 的分割之后的数组十位数
    for(var h=0;h<arrJiShu2.length;h++){
        jishu_child1.push(parseInt(arrJiShu2[h])%10);
        jishu_child2.push(parseInt(arrJiShu2[h])/10);
    }

    var sumJiShu=0; //奇数位*2 < 9 的数组之和
    var sumOuShu=0; //偶数位数组之和
    var sumJiShuChild1=0; //奇数位*2 >9 的分割之后的数组个位数之和
    var sumJiShuChild2=0; //奇数位*2 >9 的分割之后的数组十位数之和
    var sumTotal=0;
    for(var m=0;m<arrJiShu.length;m++){
        sumJiShu=sumJiShu+parseInt(arrJiShu[m]);
    }

    for(var n=0;n<arrOuShu.length;n++){
        sumOuShu=sumOuShu+parseInt(arrOuShu[n]);
    }

    for(var p=0;p<jishu_child1.length;p++){
        sumJiShuChild1=sumJiShuChild1+parseInt(jishu_child1[p]);
        sumJiShuChild2=sumJiShuChild2+parseInt(jishu_child2[p]);
    }
    //计算总和
    sumTotal=parseInt(sumJiShu)+parseInt(sumOuShu)+parseInt(sumJiShuChild1)+parseInt(sumJiShuChild2);

    //计算luhn值
    var k= parseInt(sumTotal)%10==0?10:parseInt(sumTotal)%10;
    var luhn= 10-k;

    if(lastNum==luhn){
        return true;
    }else{
        return false;
    }
}

//校验数据是否含有空格
function checkDataHaveKg(val) {
    var data_reg =/\s/;
    return data_reg.test(val);
}

//校验手机号
function checkPhoneNumber(phone) {
    var phoneReg = /(^1[3|4|5|7|8]\d{9}$)|(^09\d{8}$)/;
    return phoneReg.test(phone);
}
