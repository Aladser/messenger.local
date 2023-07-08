class TimeInfo{
    static getTimeNow(){
        let dateNow = new Date();
        let time = dateNow.getFullYear() + dateNow.getMonth()+ dateNow.getDate() + dateNow.getHours + dateNow.getMinutes + dateNow.getSeconds();
        let rslt = dateNow.getFullYear() + '.';
        rslt += dateNow.getMonth() + '.';
        rslt += dateNow.getDate() + ' ';

        rslt += dateNow.getHours() + ':';
        rslt += dateNow.getMinutes() + ':';
        rslt += dateNow.getSeconds();

        return rslt;
    }
}