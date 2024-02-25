<div style="margin: 10% auto; width: 100%;">
<div class="type-effect">
        <div class="text" id="type-text"></div>
      </div>
      <div class="row">
        <div class="col-12 p-5" style="margin: 10px auto; width: 30%">
            <img src="https://cdn.dribbble.com/users/1281958/screenshots/4862251/waiting-for-pay.gif" width="260px" alt="">
            </div>
        </div>


<form style="margin: 10px auto; width: 30%"  method="post" action="{{$data['redirectUrl']}}" id="form1" name="form1">
<input type="hidden" name="signature" value="{{$signature}}">
<input type="hidden" name="command" value="PURCHASE">
<input type="hidden" name="merchant_reference" value="{{$merchant_reference}}">
<input type="hidden" name="amount" value="{{$amount }}">
<input type="hidden" name="access_code" value ="{{ $data['access_code']}}">
<input type="hidden" name="merchant_identifier" value="{{$data['merchant_identifier']}}">
<input type="hidden" name="currency" value="{{$data['currency']}}">
<input type="hidden" name="language" value="{{$data['language']}}">
<input type="hidden" name="customer_email" value="{{$user['email']}}">
<input type="hidden" name="return_url" value="{{$data['return_url']}}">
<div class="alert alert-primary" role="alert">
    <button type="submit" style="border-radius:5px ; border-color: #fff; color: #fff; width: 200px; height: 50px; background-color: rgb(112 112 112);     font-size: 17px;font-weight: 400px; " class="btn btn-info btn-block">إصغط هنا للشحن</button>

</div>
       </form>
    </div>
