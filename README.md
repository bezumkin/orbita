## Orbita

Orbita is free open source application for creating personal blog with subscriptions.

You can think this as a simple self-hosted analog of **Patreon**, **Boosty** and **OnlyFans**.

The point is to store your content on your server without any limitations from big platform holders.
With Orbita you are able to publish your content and receive money from a grateful audience.

This application is designed to be installed on your own server using Docker.
You will not need to follow the rules of a third-party platform and pay its fees.
Payments from subscribers will be sent directly to your payment gateway.

**You own your server and all the data.**

### How to run

Download source code, copy `.env.dist` into `.env`, specify correct `SITE_URL` and maybe other settings.

Then `docker-compose up` and wait when all images will be downloaded and built.

I assume you will use some web proxy with TLS certificate to handle connections, so by default Orbita listen only localhost port `8080`.
You can change the host by adding `NGINX_HOST=0.0.0.0` into `.env`.

Admin panel is available under `your-site-url.com/admin`, login with user `admin` and password `admin`.

### Payment gateways

There are some built-in gateways located in `core/src/Services/Payments`. 
Please use it as an example for your payment connectors. They should extend abstract class `PaymentService`.

Active gateways are listed under `PAYMENT_SERVICES` setting. 
Services supporting subscriptions (periodical payments) should be also listed under `PAYMENT_SUBSCRIPTIONS`.

Don't forget to put `svg` logo of new service into `frontend/src/public/payments`. 

Payment classes are `CamelCase`, logo images are `kebab-case`.

### Issues

This is a new project, many things could be unclear, so don't hesitate to ask your questions. 
I will continue to develop this project according to your feedback!

### Screenshots
![](https://github.com/bezumkin/orbita/assets/1257284/091525ae-8cbe-4f80-9f60-3581a61536e9)
![](https://github.com/bezumkin/orbita/assets/1257284/597a4e7a-c425-4b87-8d3d-b33a46bd5dec)
![](https://github.com/bezumkin/orbita/assets/1257284/1deecfaa-8b35-49ac-a23f-2940365c2d05)
![](https://github.com/bezumkin/orbita/assets/1257284/c781c47f-3f87-49f1-b442-8684953d07ef)
![](https://github.com/bezumkin/orbita/assets/1257284/261c1d80-8474-4dcb-9a0f-16a153f2ff0a)
![](https://github.com/bezumkin/orbita/assets/1257284/a6716d52-bd2f-4319-967d-dd826810b031)
![](https://github.com/bezumkin/orbita/assets/1257284/44aa03d7-366c-46f2-824f-f66ee5d1119e)
![](https://github.com/bezumkin/orbita/assets/1257284/1f274977-b16c-426e-9808-3f9175da7ce7)
