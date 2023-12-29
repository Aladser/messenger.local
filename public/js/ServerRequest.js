/** Асинхронный запрос на сервер из Javascript*/
class ServerRequest {
    /** выполнить запрос на сервер
     * 
     * @param {*} URL адрес запроса
     * @param {*} processFunc функция обработки успешного запроса
     * @param {*} method тип запроса
     * @param {*} data данные
     * @param {*} headers заголовки
     */
    static async execute(URL, processFunc, method, errorPrg = null, data = null, headers = null) {
        let response;
        if (headers) {
            response = await fetch(URL, {
                method: method,
                headers: headers,
                body: data,
            });
        } else {
            response = await fetch(URL, {
                method: method,
                body: data,
            });
        }

        switch (response.status) {
            case 200:
                let data = await response.text();
                return processFunc(data);
            case 419:
                window.open("/419", "_self");
                break;
            case 500:
                window.open("/500", "_self");
                break;
            default:
                if (errorPrg) {
                    errorPrg.textContent = "Серверная ошибка. Подробности в консоли браузера";
                }
                console.log(response.text().then(data => console.log(data)));
        }
    }
}
