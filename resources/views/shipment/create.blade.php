@extends('layouts.base')

@section('content')
    <h1 class="text-center mt-4 mb-4">Сделайте заказ в пиццерии Lara-Pizza!</h1>

    <form method="post" action="/" enctype="application/x-www-form-urlencoded">
        @csrf

        <div class="row mb-3">
            <div class="col-sm-4">
                <label for="member_fio" class="col-form-label">Имя:</label>
                <input type="text" id="member_fio" name="member[fio]" class="form-control"
                       value="{{ $defaults['member']['fio'] }}"/>
                <div id="err_member_fio" class="invalid-feedback"></div>
            </div>
            <div class="col-sm-4">
                <label for="member_phone" class="col-form-label">Телефон:</label>
                <input type="tel" id="member_phone" name="member[phone]" class="form-control"
                       value="{{ $defaults['member']['phone'] }}"/>
                <div id="err_member_phone" class="invalid-feedback"></div>
            </div>
            <div class="col-sm-4">
                <label for="member_email" class="col-form-label">Email:</label>
                <input type="email" id="member_email" name="member[email]" class="form-control"
                       value="{{ $defaults['member']['email'] }}"/>
                <div id="err_member_email" class="invalid-feedback"></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-9">
                <label for="shipment_address" class="col-form-label">Адрес доставки:</label>
                <input type="text" id="shipment_address" name="shipment[address]" class="form-control"
                       value="{{ $defaults['shipment']['address'] }}" />
                <div id="err_shipment_address" class="invalid-feedback"></div>
            </div>
            <div class="col-sm-3">
                <label for="shipment_deliver_at" class="col-form-label">Время доставки:</label>
                <input type="time" id="shipment_deliver_at" name="shipment[deliver_at]" class="form-control"
                       value="{{ $defaults['shipment']['deliver_at'] }}"/>
                <div id="err_shipment_deliver_at" class="invalid-feedback"></div>
            </div>
        </div>

        <div id="products_list"></div>

        <p>Итого: <span id="ttl_price">0</span>&#x20bd;</p>

        <div class="row mb-3">
            <div class="col-xs-12">
                <a href="/" class="btn btn-warning">Очистить</a>
                <button type="button" class="btn btn-secondary" onclick="append_product()">Добавить товар</button>
                <button type="submit" class="btn btn-primary">OK</button>
            </div>
        </div>
    </form>
@endsection

@section('scope-scripts')
<script type="text/javascript">
    function tpl_product(n) {
        return `
<div class="wrapper_product row mb-3">
    <div class="col-sm-9">
        <label for="product_id_${n}" class="col-form-label">Товар:</label>
        <select id="product_id_${n}" name="product[id][]" class="form-control">
            <option value="0" data-price="0"></option>
@foreach($products as $p)
            <option value="{{ $p->id }}" data-price="{{ $p->price }}" >{{ $p->name }} - {{ $p->price }}&#x20bd;</option>
@endforeach
        </select>
        <div id="err_product_id_${n}" class="invalid-feedback"></div>
    </div>
    <div class="col-sm-2">
        <label for="product_cnt_${n}" class="col-form-label">Количество:</label>
        <input type="number" id="product_cnt_${n}" name="product[cnt][]" value="" class="form-control"/>
        <div id="err_product_cnt_${n}" class="invalid-feedback"></div>
    </div>
    <div class="col-sm-1">
        <label class="col-form-label">&nbsp;</label>
        <button type="button" class="close-wrapper btn btn-default form-control">&times;</button>
    </div>
</div>
`
    }

    let uniqNum = -1;
    function append_product(id, cnt) {
        $('#products_list').append(tpl_product(++uniqNum));
        if (id) $('#product_id_' + uniqNum).val(id);
        if (cnt) $('#product_cnt_' + uniqNum).val(cnt);

        $('.close-wrapper').off('click').on('click', function() {
            $(this).parents('.wrapper_product').remove();
            calc_cost();
        });

        $('.form-control').off('keyup change').on('keyup change', function() {
            const el = $(this);
            el.removeClass('is-invalid');
            if (el.attr('name').startsWith('product')) {
                calc_cost();
            }
        });
    }

    @if(!empty($defaults['product']) && !empty($defaults['product']['id']))
        @for($i = 0; $i < count($defaults['product']['id']); $i++)
            append_product({{ $defaults['product']['id'][$i] }}, {{ $defaults['product']['cnt'][$i] }});
        @endfor
    @else
        append_product();
    @endif

    const errors = {!! json_encode($errors) !!};
    for (const [k, v] of Object.entries(errors)) {
        show_error(k, v);
    }

    function show_error(id, msg) {
        $('#' + id).addClass('is-invalid');
        $('#err_' + id).html(msg);
    }

    function calc_cost() {
        let ttl = 0;
        $("select[name*='product']").each(function() {
            const elId = $(this);
            const elCnt = $('#' + elId.attr('id').replaceAll('_id_', '_cnt_'));
            const price = elId.find(':selected').attr('data-price');
            const cnt = elCnt.val();
            ttl += cnt > 0 ? price * cnt : 0;
        });
        $('#ttl_price').html(ttl);
    }

    calc_cost();
</script>
@endsection
