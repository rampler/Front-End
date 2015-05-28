!

SELECT ST_X(coordinates::geometry) as lon, ST_Y(coordinates::geometry) as lat, ST_AsText(coordinates) 
       FROM roadsegmentcoordinates  ;


 select ST_X(coordinates::geometry) as lon, ST_Y(coordinates::geometry) as lat, elev, "order", "group" from roadsegmentcoordinates


 select * from roadsegment where id in (select roadsegmentid from roadsegmentcoordinates limit 1)


 select ST_X(coordinates::geometry) as lon, ST_Y(coordinates::geometry) as lat, elev, "order", "group" from roadsegmentcoordinates order by coordinates::geometry <-> ST_GeomFromText('POINT(19.9266341385138 50.0638529383134)')