/*
select z.apt_id from (

select a.apt_id, 
		( 3959 * acos( cos( radians(b.lat) ) * cos( radians( a.lat ) ) * cos( radians( a.lon ) - radians(b.lon) )
		+ sin( radians(b.lat) ) * sin( radians( a.lat ) ) ) ) AS distance 
		FROM airports a,
		(select zip, lat, lon from zipcodes where zip in ( 
		48150,34990
		) ) b
		HAVING distance < 
		50 */


alter view vw_boxed_airports
as
select l.topic_id, a.apt_id, a.lat, a.lon, l.diffLat, l.diffLon
	,l.sendLat, l.sendLon, l.recLat, l.recLon
    ,LEAST(sendLat*1,sendLat+(-1*diffLat * (0.2*1))) as f1lat_low, GREATEST(sendLat*1,sendLat+(-1*diffLat * (0.2*1))) as f1lat_high
    ,LEAST(sendLon*1,sendLon+(-1*diffLon * (0.2*1))) as f1lon_low, GREATEST(sendLon*1,sendLon+(-1*diffLon * (0.2*1))) as f1lon_high
    
    ,LEAST(sendLat+(-1*diffLat * (0.2*1)),sendLat+(-1*diffLat * (0.2*2))) as f2lat_low, GREATEST(sendLat+(-1*diffLat * (0.2*1)),sendLat+(-1*diffLat * (0.2*2))) as f2lat_high
    ,LEAST(sendLon+(-1*diffLon * (0.2*1)),sendLon+(-1*diffLon * (0.2*2))) as f2lon_low, GREATEST(sendLon+(-1*diffLon * (0.2*1)),sendLon+(-1*diffLon * (0.2*2))) as f2lon_high    
    
    ,LEAST(sendLat+(-1*diffLat * (0.2*2)),sendLat+(-1*diffLat * (0.2*3))) as f3lat_low, GREATEST(sendLat+(-1*diffLat * (0.2*2)),sendLat+(-1*diffLat * (0.2*3))) as f3lat_high
    ,LEAST(sendLon+(-1*diffLon * (0.2*2)),sendLon+(-1*diffLon * (0.2*3))) as f3lon_low, GREATEST(sendLon+(-1*diffLon * (0.2*2)),sendLon+(-1*diffLon * (0.2*3))) as f3lon_high    
    
    ,LEAST(sendLat+(-1*diffLat * (0.2*3)),sendLat+(-1*diffLat * (0.2*4))) as f4lat_low, GREATEST(sendLat+(-1*diffLat * (0.2*3)),sendLat+(-1*diffLat * (0.2*4))) as f4lat_high
    ,LEAST(sendLon+(-1*diffLon * (0.2*3)),sendLon+(-1*diffLon * (0.2*4))) as f4lon_low, GREATEST(sendLon+(-1*diffLon * (0.2*3)),sendLon+(-1*diffLon * (0.2*4))) as f4lon_high   
    
    -- 
    ,LEAST(sendLat+(-1*diffLat * (0.1)),sendLat+(-1*diffLat * (0.3))) as over1lat_low, GREATEST(sendLat+(-1*diffLat * (0.1)),sendLat+(-1*diffLat * (0.3))) as over1lat_high
    ,LEAST(sendLon+(-1*diffLon * (0.1)),sendLon+(-1*diffLon * (0.3))) as over1lon_low, GREATEST(sendLon+(-1*diffLon * (0.1)),sendLon+(-1*diffLon * (0.3))) as over1lon_high
    ,LEAST(sendLat+(-1*diffLat * (0.3)),sendLat+(-1*diffLat * (0.5))) as over2lat_low, GREATEST(sendLat+(-1*diffLat * (0.3)),sendLat+(-1*diffLat * (0.5))) as over2lat_high
    ,LEAST(sendLon+(-1*diffLon * (0.3)),sendLon+(-1*diffLon * (0.5))) as over2lon_low, GREATEST(sendLon+(-1*diffLon * (0.3)),sendLon+(-1*diffLon * (0.5))) as over2lon_high
    ,LEAST(sendLat+(-1*diffLat * (0.4)),sendLat+(-1*diffLat * (0.7))) as over3lat_low, GREATEST(sendLat+(-1*diffLat * (0.4)),sendLat+(-1*diffLat * (0.7))) as over3lat_high
    ,LEAST(sendLon+(-1*diffLon * (0.4)),sendLon+(-1*diffLon * (0.7))) as over3lon_low, GREATEST(sendLon+(-1*diffLon * (0.4)),sendLon+(-1*diffLon * (0.7))) as over3lon_high
    ,LEAST(sendLat+(-1*diffLat * (0.5)),cast(recLat as decimal(12,8))) as over4lat_low, GREATEST(sendLat+(-1*diffLat * (0.5)),cast(recLat as decimal(12,8))) as over4lat_high
    ,LEAST(sendLon+(-1*diffLon * (0.5)),cast(recLon as decimal(12,8))) as over4lon_low, GREATEST(sendLon+(-1*diffLon * (0.5)),cast(recLon as decimal(12,8))) as over4lon_high
    
	,l.topic_title,l.last_post, l.last_post_human, l.sendCity, l.recCity
    ,( 3959 * acos( cos( radians(l.sendLat) ) * cos( radians( a.lat ) ) * cos( radians( a.lon ) - radians(l.sendLon) )
								+ sin( radians(l.sendLat) ) * sin( radians( a.lat ) ) ) ) as distance_from_send
	,( 3959 * acos( cos( radians(l.recLat) ) * cos( radians( a.lat ) ) * cos( radians( a.lon ) - radians(l.recLon) )
								+ sin( radians(l.recLat) ) * sin( radians( a.lat ) ) ) ) as distance_from_rec
from airports a, 
	vw_lines l
where 1=1   
	and (
	   (a.lat between LEAST(sendLat,sendLat+(-1*diffLat * (0.2*1))) and GREATEST(sendLat,sendLat+(-1*diffLat * (0.2*1)))
	and a.lon between LEAST(sendLon,sendLon+(-1*diffLon * (0.2*1))) and GREATEST(sendLon,sendLon+(-1*diffLon * (0.2*1))) )
    or (a.lat between LEAST(sendLat+(-1*diffLat * (0.2*1)),sendLat+(-1*diffLat * (0.2*2))) and GREATEST(sendLat+(-1*diffLat * (0.2*1)),sendLat+(-1*diffLat * (0.2*2)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.2*1)),sendLon+(-1*diffLon * (0.2*2))) and GREATEST(sendLon+(-1*diffLon * (0.2*1)),sendLon+(-1*diffLon * (0.2*2))) )
    or (a.lat between LEAST(sendLat+(-1*diffLat * (0.2*2)),sendLat+(-1*diffLat * (0.2*3))) and GREATEST(sendLat+(-1*diffLat * (0.2*2)),sendLat+(-1*diffLat * (0.2*3)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.2*2)),sendLon+(-1*diffLon * (0.2*3))) and GREATEST(sendLon+(-1*diffLon * (0.2*2)),sendLon+(-1*diffLon * (0.2*3))) )
	or (a.lat between LEAST(sendLat+(-1*diffLat * (0.2*3)),sendLat+(-1*diffLat * (0.2*4))) and GREATEST(sendLat+(-1*diffLat * (0.2*3)),sendLat+(-1*diffLat * (0.2*4)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.2*3)),sendLon+(-1*diffLon * (0.2*4))) and GREATEST(sendLon+(-1*diffLon * (0.2*3)),sendLon+(-1*diffLon * (0.2*4))) )
	or (a.lat between LEAST(sendLat+(-1*diffLat * (0.2*4)),cast(recLat as decimal(12,6))) 						and GREATEST(sendLat+(-1*diffLat * (0.2*4)),cast(recLat as decimal(12,6)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.2*4)),cast(recLon as decimal(12,6))) 						and GREATEST(sendLon+(-1*diffLon * (0.2*4)),cast(recLon as decimal(12,6))) )
    or ( 3959 * acos( cos( radians(l.sendLat) ) * cos( radians( a.lat ) ) * cos( radians( a.lon ) - radians(l.sendLon) )
								+ sin( radians(l.sendLat) ) * sin( radians( a.lat ) ) ) ) < 50
    or ( 3959 * acos( cos( radians(l.recLat) ) * cos( radians( a.lat ) ) * cos( radians( a.lon ) - radians(l.recLon) )
								+ sin( radians(l.recLat) ) * sin( radians( a.lat ) ) ) ) < 50    
	-- below creates overlapping boxes
	or (a.lat between LEAST(sendLat+(-1*diffLat * (0.1)),sendLat+(-1*diffLat * (0.3))) and GREATEST(sendLat+(-1*diffLat * (0.1)),sendLat+(-1*diffLat * (0.3)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.1)),sendLon+(-1*diffLon * (0.3))) and GREATEST(sendLon+(-1*diffLon * (0.1)),sendLon+(-1*diffLon * (0.3))) )
    or (a.lat between LEAST(sendLat+(-1*diffLat * (0.3)),sendLat+(-1*diffLat * (0.6))) and GREATEST(sendLat+(-1*diffLat * (0.3)),sendLat+(-1*diffLat * (0.6)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.3)),sendLon+(-1*diffLon * (0.6))) and GREATEST(sendLon+(-1*diffLon * (0.3)),sendLon+(-1*diffLon * (0.6))) )
	or (a.lat between LEAST(sendLat+(-1*diffLat * (0.4)),sendLat+(-1*diffLat * (0.7))) and GREATEST(sendLat+(-1*diffLat * (0.4)),sendLat+(-1*diffLat * (0.7)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.4)),sendLon+(-1*diffLon * (0.7))) and GREATEST(sendLon+(-1*diffLon * (0.5)),sendLon+(-1*diffLon * (0.7))) )

	or (a.lat between LEAST(sendLat+(-1*diffLat * (0.5)),sendLat+(-1*diffLat * (0.7))) and GREATEST(sendLat+(-1*diffLat * (0.5)),sendLat+(-1*diffLat * (0.7)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.5)),sendLon+(-1*diffLon * (0.7))) and GREATEST(sendLon+(-1*diffLon * (0.5)),sendLon+(-1*diffLon * (0.7))) )


	or (a.lat between LEAST(sendLat+(-1*diffLat * (0.45)),cast(recLat as decimal(12,8))) and GREATEST(sendLat+(-1*diffLat * (0.45)),cast(recLat as decimal(12,8)))
	and a.lon between LEAST(sendLon+(-1*diffLon * (0.5)),cast(recLon as decimal(12,8))) and GREATEST(sendLon+(-1*diffLon * (0.5)),cast(recLon as decimal(12,8))) )
	)  -- or apt_id = 'KDVK'
    