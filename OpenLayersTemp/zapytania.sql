-

select roadsegmentid from roadsegmentcoordinates where point(1,1) <-> coordinates < 100 order by point(1,1) <-> coordinates asc limit 1


select * from roadsegment where id in (select roadsegmentid from roadsegmentcoordinates where point(1,1) <-> coordinates < 100 order by point(1,1) <-> coordinates asc limit 1)
