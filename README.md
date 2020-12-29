# REST API магазина NoName
### [Рабочий пример](https://kepo4kapro.gq/)
## Запросы
### GET Получить список товаров /catalog/<название каталога>
```js
    RESPONSE: {
      name: Название,
      content: 
          [
            {
              id: Номер товара,
              name: Название товара,
              price: Цена,
              main_pict: Основное изоброжение
            }
          ]
    }
```
### GET Получить товар /product/<Номер товара>
```js
    RESPONSE: {
      name: Название,
      description: Описание,
      composition: [
          {
              name: Имя,
              part: Процентное содержание
          }
      ],
      sizes: [
          {
              s: Название размера (s),
              x: Множитель цены
          }
      ],
      price: Цена,
      main_pict: Основное изображение,
      others_pict: [Другие изоброжения],
      catalog: Каталог
  }
```
### GET Получить размерную таблицу каталога /vmodals_sizes/<название каталога>
```js
    RESPONSE: {
      description: [" Строки описания "],
      sizes:  [ Доступные размеры],
      dimen : [ Доступные метки имерения],
      data :  {
         Размер: [Значения меток]
      }
  }
```
### GET Получить данные аккаунта /account
```js
    REQUEST
    HEADERS: {
        Authorization: "Bearer token"
  }
```
```js
    RESPONSE: {
        account: {
            login: Логин,
            phone: Телефон,
            mail: Почта
        },
        delivery: {
            name: ФИО получателя,
            uindex: Индекс ПО,
            edge: Район,
            county: Город,
            streets: Улица,
            house: Дом,
            apartment: Квартира
        }
  }
```
### POST Регистрация /register
```js
    REQUEST
    POST: {
        login: Логин,
        mailP: Почта или Телефон,
        pass: Пароль,
        passC: Пароль подтверждения
  }
```
```js
    RESPONSE:{
        422:
        {
           error: 
           {
              message: "Validation error",
              errors: ["Массив ошибок"]
           }
        },
        200:
        {
           id: "Номер пользовотеля",
           token: "Bearer token"
        }
  }
```
### POST Вход /login
```js
    REQUEST
    POST: {
        login: "Логин, телефон или почта",
        pass: Пароль,
  }
```
```js
    RESPONSE:{
        422:
        {
           error: 
           {
              message: "Validation error",
              errors: ["Массив ошибок"]
           }
        },
        400:
        {
           error: 
           {
              message: "login or password invalid"
           }
        },
        200:
        {
           id: "Номер пользовотеля",
           token: "Bearer token"
        }
  }
```
### POST Выход /logout
```js
    REQUEST
    HEADERS: {
        Authorization: "Bearer token"
  }
```
