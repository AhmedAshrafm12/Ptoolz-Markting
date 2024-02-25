@if($status == 0)
    <div>failed</div>
@else
<div class="card">
    <div class="card-body mx-4">
      <div class="container">
        <p class="my-5 mx-5" style="font-size: 30px;">Thank you for your reservation. Booked!</p>
        <div class="row">
          <ul class="list-unstyled">
            <li class="text-black"> {{ $fullName }} </li>
            <li class="text-muted mt-1"><span class="text-black">Invoice</span> # $pid </li>
            <li class="text-black mt-1"> date('now')</li>
          </ul>
          <hr>
          <div class="col-xl-10">
            <p>Price</p>
          </div>
          <div class="col-xl-2">
            <p class="float-end">  {{ $amount }}
            </p>
          </div>
          <hr>
        </div>
        <div class="row">
          <div class="col-xl-10">
            <p>Booking price  %</p>
          </div>
          <div class="col-xl-2">
            <p class="float-end"> {{ $amount  }}
            </p>
          </div>
          <hr>
        </div>

        <div class="row text-black">

          <div class="col-xl-12">
            <p class="float-end fw-bold">Total:  {{ $amount }}
            </p>
          </div>
          <hr style="border: 2px solid black;">
        </div>
        <div class="text-center" style="margin-top: 90px;">
          <p>After payment, please close the page and take a screenshot of the bill . </p>
        </div>

      </div>
    </div>
  </div>
  @endif
