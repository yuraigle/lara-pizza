<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class ShipmentController extends BaseController
{
    public function create(Request $request)
    {
        $rows = DB::select('select `id`, `name`, `price` from `products` order by `type`, `id`');
        $products = [];
        foreach ($rows as $row) {
            $products[$row->id] = $row;
        }

        $req = [
            'member' => ['fio' => '', 'phone' => '', 'email' => ''],
            'shipment' => ['address' => '', 'deliver_at' => ''],
            'product' => ['id' => [], 'cnt' => []],
        ];
        $errors = [];

        if ($request->isMethod('post')) {
            $req = $request->post();

            // валидация всех полей
            if (empty($req['member']['fio'])) {
                $errors['member_fio'] = 'Представьтесь';
            } elseif (preg_match('|[^А-Яа-яЁё\-\s]|u', $req['member']['fio'])) {
                $errors['member_fio'] = 'Некорректные знаки в имени';
            }

            $phone = preg_replace('|^\+7|', '8', $req['member']['phone']);
            $phone = preg_replace('|[()\-\s]|', '', $phone);
            if (empty($req['member']['phone'])) {
                $errors['member_phone'] = 'Номер телефона для связи обязателен';
            } elseif (!preg_match('|^\d{11}$|', $phone)) {
                $errors['member_phone'] = 'Мобильный телефон - 11 цифр';
            }

            if (!empty($req['member']['email']) && !filter_var($req['member']['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['member_email'] = 'Невалидный адрес эл. почты';
            }

            if (empty($req['shipment']['address'])) {
                $errors['shipment_address'] = 'Адрес доставки обязателен';
                // его надо распознавать по ГАР/ФИАС, но я не буду
            }

            $deliverDt = new \DateTime();
            if (empty($req['shipment']['deliver_at'])) {
                $errors['shipment_deliver_at'] = 'Время доставки обязательно';
            } elseif (!preg_match('|^\d{1,2}:\d{1,2}$|', $req['shipment']['deliver_at'])) {
                $errors['shipment_deliver_at'] = 'Некорректное время доставки';
            } else {
                [$hour, $min] = preg_split('|:|', $req['shipment']['deliver_at']);
                if ($hour > 22 || $hour < 8) {
                    $errors['shipment_deliver_at'] = 'Ночью не доставляем';
                } else {
                    $deliverDt->setTime($hour, $min);

                    $now = new \DateTime();
                    $nearestTime = $now->add(new \DateInterval('PT30M'));
                    if ($deliverDt < $now) {
                        // пока демо и время на сервере может глючить - эти проверки уберу
                        // $errors['shipment_deliver_at'] = 'Время прошло';
                    } elseif ($deliverDt < $nearestTime) {
                        // $errors['shipment_deliver_at'] = 'Не успеем';
                    }
                }
            }

            $totalPrice = 0;
            if (empty($req['product']) || empty($req['product']['id'])) {
                $errors['product_id_0'] = 'Выберите товар';
                $errors['product_cnt_0'] = 'Некорректное кол-во';
            } else {
                for ($i = 0; $i < count($req['product']['id']); $i++) {
                    if (!isset($req['product']['id'][$i])
                        || !is_numeric($req['product']['id'][$i])
                        || !in_array($req['product']['id'][$i], array_keys($products)) // ИД есть в БД
                    ) {
                        $errors['product_id_' . $i] = 'Выберите товар';
                    } elseif (!isset($req['product']['cnt'][$i])
                        || !is_numeric($req['product']['cnt'][$i])
                        || $req['product']['cnt'][$i] <= 0
                    ) {
                        $errors['product_cnt_' . $i] = 'Некорректное кол-во';
                    } else {
                        $totalPrice += $products[$req['product']['id'][$i]]->price * $req['product']['cnt'][$i];
                    }
                }
            }

            if (empty($errors)) {
                // ок, сохраняем
                DB::beginTransaction();

                $row = DB::selectOne('select id from `members` where `phone` = ?', [$req['member']['phone']]);
                if ($row) {
                    // существующий пользователь
                    $uid = $row->id;
                    DB::update('update `members` set `fio` = ?, `email` = ? where `id` = ?',
                        [$req['member']['fio'], $req['member']['email'], $uid]);
                } else {
                    // новый
                    DB::insert('insert into `members` (`phone`, `fio`, `email`) values (?,?,?)',
                        [$req['member']['phone'], $req['member']['fio'], $req['member']['email']]);
                    $uid = DB::getPdo()->lastInsertId();
                }

                DB::insert('insert into `shipments` (`address`, `total_price`, `deliver_at`, `member_id`) values (?,?,?,?)',
                    [$req['shipment']['address'], $totalPrice, $deliverDt, $uid]); // TODO
                $sid = DB::getPdo()->lastInsertId();

                for ($i = 0; $i < count($req['product']['id']); $i++) {
                    DB::insert('insert into `shipments_products` (`shipment_id`, `product_id`, `cnt`) values (?,?,?)',
                        [$sid, (int)$req['product']['id'][$i], (int)$req['product']['cnt'][$i]]);
                }

                DB::commit();

                return redirect('/await?id=' . $sid);
            }
        }

        return view('shipment.create', ['products' => $products, 'defaults' => $req, 'errors' => $errors]);
    }

    public function await(Request $request)
    {
        $id = (int)$request->get('id');
        $row = DB::selectOne('select m.`fio`, `total_price`, `deliver_at` from `shipments` s
    left join `members` m on m.id = s.member_id where s.id = ?', [$id]);

        return view('shipment.await', ['id' => $id, 'row' => $row]);
    }
}
