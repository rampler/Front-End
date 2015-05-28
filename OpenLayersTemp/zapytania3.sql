!

SELECT ST_X(coordinates::geometry) as lon, ST_Y(coordinates::geometry) as lat, ST_AsText(coordinates) 
       FROM roadsegmentcoordinates  ;


 select ST_X(coordinates::geometry) as lon, ST_Y(coordinates::geometry) as lat, elev, "order", "group" from roadsegmentcoordinates


 select * from roadsegment where id in (select roadsegmentid from roadsegmentcoordinates limit 1)


 select ST_X(coordinates::geometry) as lon, ST_Y(coordinates::geometry) as lat, elev, "order", "group", (ST_DISTANCE(ST_TRANSFORM(coordinates::geometry,2163), ST_TRANSFORM(ST_GeomFromText('POINT(19.927195591958 50.0635036649045)',4326),2163))) as dist from roadsegmentcoordinates order by coordinates::geometry <-> ST_GeomFromText('POINT(19.927195591958 50.0635036649045)',4326) asc limit 1