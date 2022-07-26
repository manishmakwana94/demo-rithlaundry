 <meta name="csrf-token" content="{{ Session::token() }}"> 
<div class="container">
     <div class="col-md-2 col-md-offset-10">
        <a href='{{URL::to("/")}}/admin/zones' class='btn btn-info pull-right' style='margin-right:20px;'>Back</a>
    </div>
<style>
#map {
  height: 700px;
  width: 100%;
  margin-top: 10px;
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

  var drawingManager = new google.maps.drawing.DrawingManager({
    drawingMode: google.maps.drawing.OverlayType.POLYGON,
    drawingControl: true,
    drawingControlOptions: {
      position: google.maps.ControlPosition.TOP_CENTER,
      drawingModes: ['polygon'],
    },
    polygonOptions: {
      fillColor: "#FA8072 ",
      fillOpacity: 0.5,
      strokeWeight: 3,
      clickable: true,
      editable: false,
      zIndex: 1,
    },
  });

  google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
   drawingManager.setMap(null);
      
    for (var i = 0; i < polygon.getPath().getLength(); i++) {
      coordStr += polygon.getPath().getAt(i).toUrlValue(6) + ";";
    }
    storeArea(coordStr)
  });
  drawingManager.setMap(map);
};

function storeArea(coordStr){

jQuery.ajax({
  url: "{{ route('admin.save_polygon') }}",
  type:"POST",
  data:{
    coordStr:coordStr,
    id: '{{$id}}',
    _token: '{{ csrf_token() }}'
  },
  success:function(response){
    if(response) {
      jQuery('.success').text(response.success);
    }
  },
  error: function(error) {
 
  }
 });
}
google.maps.event.addDomListener(window, "load", initMap);

// function save_polygon(){
//    $.post('../../save_polygon',
//     {
//         '_token': $('meta[name=csrf-token]').attr('content'),
//         id: {!! $id !!},
//         polygon: coordStr,
//     })
//     .error(
        
//      )
//     .success(
        
//      );
// }




</script>
</div>