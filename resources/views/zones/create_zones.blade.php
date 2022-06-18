 <meta name="csrf-token" content="{{ Session::token() }}"> 
<div class="container">
     <div class="col-md-2 col-md-offset-10">
        <a href='/admin/zones' class='btn btn-info pull-right' style='margin-right:20px;'>Back</a>
    </div>
<style>
#map {
  height: 500px;
  width: 100%;
  margin-top: 50px;
  padding: 0px
}
</style>

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
      lat: 41.29115910020692,
      lng: -96.0156571511163
    },
    zoom: 10,
    
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
      clickable: false,
      editable: true,
      zIndex: 1,
    },
  });

  google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
   drawingManager.setMap(null);
    
    for (var i = 0; i < polygon.getPath().getLength(); i++) {
      coordStr += polygon.getPath().getAt(i).toUrlValue(6) + ";";
    }
    save_polygon();
    console.log(coordStr);
  });
  drawingManager.setMap(map);
};


function save_polygon(){
   $.post('../../save_polygon',
    {
        '_token': $('meta[name=csrf-token]').attr('content'),
        id: {!! $id !!},
        polygon: coordStr,
    })
    .error(
        
     )
    .success(
        
     );
}


google.maps.event.addDomListener(window, "load", initMap);


</script>
</div>