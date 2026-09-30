import http from "k6/http";
import { check, sleep } from "k6";


export const options = {

    stages: [
        {
            duration: "30s",
            target: 50
        },

        {
            duration: "1m",
            target: 100
        },

        {
            duration: "1m",
            target: 250
        },

        {
            duration: "30s",
            target: 500
        },

        {
            duration: "30s",
            target: 0
        }
    ],


    thresholds: {

        // نسبة الأخطاء أقل من 1%
        http_req_failed: [
            "rate<0.01"
        ],

        // 95% من الطلبات أقل من 500ms
        http_req_duration: [
            "p(95)<500"
        ]

    }

};


export default function () {


    // Home API
    let home = http.get(
        "http://127.0.0.1:8000/api/home"
    );


    check(home, {

        "home API status 200":
            (r) => r.status === 200,

    });


    sleep(1);



    // Stores API
    let stores = http.get(
        "http://127.0.0.1:8000/api/stores"
    );


    check(stores, {

        "stores API status 200":
            (r) => r.status === 200,

    });


    sleep(1);



    // Store Products API
    // غير رقم المتجر حسب الـ ID الموجود عندك
    let products = http.get(
        "http://127.0.0.1:8000/api/stores/1/products"
    );


    check(products, {

        "products API status 200":
            (r) => r.status === 200,

    });


    sleep(2);

}
