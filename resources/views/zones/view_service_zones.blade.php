 <meta name="csrf-token" content="{{ Session::token() }}"> 
<div class="container">
     <div class="col-md-2 col-md-offset-10">
        <a href='{{URL::to("/")}}/admin/zones' class='btn btn-info pull-right' style='margin-right:20px;'>Back</a>
    </div>
<style>
#map {
  height: 700px;
  width: 100%;
  margin-top: 50px;
  padding: 0px
}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?libraries=drawing&key={{ env('MAP_KEY') }}"></script>

<div id="map"></div>
<script>

   (function()
{
  if( window.localStorage )
  {
    if( !localStorage.getItem('firstLoad') )
    {
      localStorage['firstLoad'] = true;
      window.location.reload();
    }  
    else
      localStorage.removeItem('firstLoad');
  }
})();

var coordStr = "";

function initMap() {
 
  var map = new google.maps.Map(document.getElementById('map'), {
    center: {
      lat: 23.4288807,
      lng: 74.4468653
    },
    zoom: 6,
    
  });

var triangleCoords = []

  jQuery.ajax({
  url: "{{ route('admin.get_polygon') }}",
  type:"POST",
  dataType: "JSON",
    async: false,
  data:{
    id: '{{$id}}',
    _token: '{{ csrf_token() }}'
  },
  success:function(response){
      var mydata = response.data;
      for (var i=0; i<mydata.length; i++) {
          triangleCoords[i] = new google.maps.LatLng(mydata[i].lat, mydata[i].lng);
      }
 
  },
  error: function(error) {
 
  }
 });
  const bermudaTriangle = new google.maps.Polygon({
    paths: triangleCoords,
    strokeColor: "#FF0000",
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: "#FF0000",
    fillOpacity: 0.35,
  });

  bermudaTriangle.setMap(map);
 
};
google.maps.event.addDomListener(window, "load", initMap);
</script>
</div>