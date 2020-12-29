# REST API магазина NoName
### [Рабочий пример](https://kepo4kapro.gq/)
## Запросы
### Получить список товаров /catalog/<название каталога>
```js
    ANSWER: {
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
### Получить товар /product/<Номер товара>
```js
    ANSWER: {
      name: Название,
      description: Описание,
      composition: [
          {
              name: ,
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
