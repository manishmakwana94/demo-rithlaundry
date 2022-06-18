<div class="container">
    <div class="col-lg-6">
        <div class="table-responsive">          
          <table class="table">
            <tbody>
              <tr>
                <th>Order Id</th>
                <td>{{$order_id}}</td>
              </tr>
              <tr>
                <th>Customer Name</th>
                <td>{{$customer_name}}</td>
              </tr>
              <tr>
                <th>Address</th>
                <td>{{$address}}</td>
              </tr>
              <tr>
                <th>Delivery Date</th>
                <td>{{$delivery_date}}</td>
              </tr>
              <tr>
                <th>Delivery Time</th>
                <td>{{$delivery_time}}</td>
              </tr>
              <tr>
                <th>Pickup Date</th>
                <td>{{$pickup_date}}</td>
              </tr>
              <tr>
                <th>Pickup Time</th>
                <td>{{$pickup_time}}</td>
              </tr>
              <tr>
                <th>Delivered By</th>
                <td>{{$delivered_by}}</td>
              </tr>
              <tr>
                <th>Payment Mode</th>
                <td>{{$payment_mode}}</td>
              </tr>
              <tr>
                <th>Sub Total</th>
                <td>{{$sub_total}}</td>
              </tr>
              <tr>
                <th>Discount</th>
                <td>{{$discount}}</td>
              </tr>
              <tr>
                <th>Total</th>
                <td>{{$total}}</td>
              </tr>
              <tr>
                <th>Status</th>
                <td>{{$status}}</td>
              </tr>
            </tbody>
          </table>
        </div>
    </div>
    <div class="col-md-2 col-md-offset-2">
        <a href='/admin/orders' class='btn btn-info pull-right' style='margin-right:20px;'>Back</a>
    </div>
    <div class="col-lg-12">
        <h3>Items</h3>
        <table class="table table-hover">
            <thead>
              <tr>
                <th>Service</th>
                <th>Product</th>
                <th>Qty</th>
              </tr>
            </thead>
            <tbody>
            @foreach($order_items as $value)
              <tr>
                <td>{{ $value->service_name }}</td>
                <td>{{ $value->product_name }}</td>
                <td>{{ $value->qty }}</td>
              </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>