@extends('layouts.base')

@section('content')
    {{ $row->fio }}! Ваш заказ #{{ $id }} прибудет к вам {{ $row->deliver_at }} <br/>
    Оплата {{ $row->total_price }} &#x20bd; курьеру. <br/>
    Ожидайте.<br/>

    <p><a href="/">Назад</a></p>
@endsection
