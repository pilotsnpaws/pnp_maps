DROP FUNCTION fn_distance;

DELIMITER $$

CREATE FUNCTION fn_distance(p_fromLat double, p_fromLon double,
	 p_toLat double, p_toLon double) RETURNS double
BEGIN

DECLARE distance float(11,6);

SET distance = (3959 * acos( cos( radians(p_fromLat) ) 
   * cos( radians(p_toLat) ) 
   * cos( radians(p_toLon) - radians(p_fromLon)) + sin(radians(p_fromLat)) 
   * sin( radians(p_toLat)))); 

RETURN round(distance,0);

END

DELIMITER ;

select fn_distance(39.588972, -84.224861, 39.600056, -84.416611)

ck the manual that corresponds to your MySQL server version 
for the right syntax to use near '' at line 6
