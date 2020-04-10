@extends('layouts.app')

@section('content')

<div class="container">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <h2>Dados para Pagamento</h2>
            </div>
        </div>
        <form action="" method="post">
            <div class="row">
                <div class="col-md-12 form-group">
                    <label>Nome no Cartão <span class="brand"></span></label>
                    <input type="text" class="form-control" name="card_name">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label>Número do Cartão <span class="brand"></span></label>
                    <input type="text" class="form-control" name="card_number">
                    <input type="hidden" name="card_brand">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Mês de Expiração</label>
                    <input type="text" class="form-control" name="card_month">
                </div>
                <div class="col-md-4 form-group">
                    <label>Ano de Expiração</label>
                    <input type="text" class="form-control" name="card_year">
                </div>
            </div>

            <div class="row">
                <div class="col-md-5 form-group">
                    <label>Código de Segurança</label>
                    <input type="text" class="form-control" name="card_cvv">
                </div>
            </div>

            <button class="btn btn-success btn-lg processCheckout">Efetuar Pagamento</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js"></script>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js"
            integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
            crossorigin="anonymous"></script>
    <script>
        const sessionId = '{{session()->get('pagseguro_session_code')}}';
        PagSeguroDirectPayment.setSessionId(sessionId);
    </script>
    <script>
        let cardNumber = document.querySelector('input[name=card_number]');
        let spanBrand = document.querySelector('span.brand');
        cardNumber.addEventListener('keyup', function () {
            if(cardNumber.value.length >= 6) {
                PagSeguroDirectPayment.getBrand({
                    cardBin: cardNumber.value.substr(0,6),
                    success: function (res) {
                        let imgFlag= `<img src="https://stc.pagseguro.uol.com.br/public/img/payment-methods-flags/68x30/${res.brand.name}.png">`
                        spanBrand.innerHTML = imgFlag;
                        document.querySelector('input[name=card_brand]').value = res.brand.name;
                    },
                    error: function (err) {
                        console.log(err);
                    },
                    complete: function (res) {
                        console.log('Complete:', res);
                    }
                });
            }
        });

        let submitButton = document.querySelector('button.processCheckout');

        submitButton.addEventListener('click', function (event) {

            event.preventDefault();

            PagSeguroDirectPayment.createCardToken({
               cardNumber: document.querySelector('input[name=card_number]').value,
                brand: document.querySelector('input[name=card_brand]').value,
                cvv: document.querySelector('input[name=card_cvv]').value,
                expirationMonth: document.querySelector('input[name=card_month]').value,
                expirationYear: document.querySelector('input[name=card_year]').value,
                success: function (res) {
                    proccessPayment(res.card.token);
                },
                error: function (err) {
                    console.log(err);
                },
            });
        });
        function proccessPayment(token) {
            let data = {
                card_token: token,
                card_name: document.querySelector('input[name=card_name]').value,
                hash: PagSeguroDirectPayment.getSenderHash(),
                _token: '{{csrf_token()}}'
            };

            $.ajax({
               type: 'POST',
                url: '{{route("subscription.proccess")}}',
                data: data,
                dataType: 'json',
                success: function (res) {
                    console.log(res);
                },
                error: function (err) {
                    console.log(err);
                },
            });
        }

    </script>
@endsection
