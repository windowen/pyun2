DELETE FROM `live_m5`.`game_manage` WHERE integrator_code LIKE '%GSC%';


SELECT id,game_code,game_name,ext_name,integrator_code,is_del FROM `live_m5`.`game_manage`
WHERE game_name LIKE '%TREASURE RAIDERS%'
   OR game_name LIKE '%Robin Hood%'
   OR game_name LIKE '%GLORY OF ROME%'
   OR game_name LIKE '%GRAND BLUE%'
   OR game_name LIKE '%RICH MAN%'
   OR game_name LIKE '%GOLD RUSH%'
   OR game_name LIKE '%MAGIC BEANS%'
   OR game_name LIKE '%WAR OF THE UNIVERSE%'
   OR game_name LIKE '%HOT POT PARTY%'
   OR game_name LIKE '%GODS GRANT FORTUNE%'
   OR game_name LIKE '%BAO CHUAN FISHING%'
   OR game_name LIKE '%FORTUNE MONEY BOOM%'
   OR game_name LIKE '%FORTUNE GODDESS%'
   OR game_name LIKE '%SUPER ELEMENTS%'
   OR game_name LIKE '%NIGHT MARKET 2%'
   OR game_name LIKE '%LEGEND OF INCA%'
   OR game_name LIKE '%FORTUNE EGG%'
   OR game_name LIKE '%WIN WIN NEKO%'
   OR game_name LIKE '%CIRCUS DOZER%'
   OR game_name LIKE '%MONEY TREE DOZER%'
   OR game_name LIKE '%ZEUS%'
   OR game_name LIKE '%CRAZY BUFFALO%'
   OR game_name LIKE '%GOLDEN GENIE%'
   OR game_name LIKE '%SUGAR BANG BANG%';
	 
	 
	 
	 UPDATE `live_m5`.`game_manage`
SET 
  sort = '{"BR": {"13": 0}}',
  game_type_wsid = '{"BR": [13]}',
  is_del = '{"BR": 0, "TH": 0, "VN": 0}',
  status = '{"BR": {"2": 0, "11": 0, "13": 0}, "TH": {"1": 0, "2": 0}}',
  support_currency = 'BRL,THB,VND',
  support_country = 'BR,TH,VN',
  arrange_style = '{"BR": {"2": 3, "11": 3, "13": 3}}'
WHERE 
  game_name LIKE '%TREASURE RAIDERS%' OR
  game_name LIKE '%Robin Hood%' OR
  game_name LIKE '%GLORY OF ROME%' OR
  game_name LIKE '%GRAND BLUE%' OR
  game_name LIKE '%RICH MAN%' OR
  game_name LIKE '%GOLD RUSH%' OR
  game_name LIKE '%MAGIC BEANS%' OR
  game_name LIKE '%WAR OF THE UNIVERSE%' OR
  game_name LIKE '%HOT POT PARTY%' OR
  game_name LIKE '%GODS GRANT FORTUNE%' OR
  game_name LIKE '%BAO CHUAN FISHING%' OR
  game_name LIKE '%FORTUNE MONEY BOOM%' OR
  game_name LIKE '%FORTUNE GODDESS%' OR
  game_name LIKE '%SUPER ELEMENTS%' OR
  game_name LIKE '%NIGHT MARKET 2%' OR
  game_name LIKE '%LEGEND OF INCA%' OR
  game_name LIKE '%FORTUNE EGG%' OR
  game_name LIKE '%WIN WIN NEKO%' OR
  game_name LIKE '%CIRCUS DOZER%' OR
  game_name LIKE '%MONEY TREE DOZER%' OR
  game_name LIKE '%ZEUS%' OR
  game_name LIKE '%CRAZY BUFFALO%' OR
  game_name LIKE '%GOLDEN GENIE%' OR
  game_name LIKE '%SUGAR BANG BANG%';



UPDATE `live_m5`.`game_manage`
SET 
  sort = '{"BR": {"13": 0}}',
  game_type_wsid = '{"BR": [13]}',
  is_del = '{"BR": 1, "TH": 1, "VN": 1}',
  status = '{"BR": {"2": 0, "11": 0, "13": 0}, "TH": {"1": 0, "2": 0}}',
  support_currency = 'BRL,THB,VND',
  support_country = 'BR,TH,VN',
  arrange_style = '{"BR": {"2": 3, "11": 3, "13": 3}}'
WHERE id IN (
  3373, 3391, 3573, 3607, 3768, 3997, 4069, 4107,
  4460, 4474, 4668, 5242, 5355, 5476, 5539,3184,3241
);

-- 更新到fc中去
UPDATE `live_m5`.`game_manage` 
SET sort = '{"BR": {"12": 0}}',
game_type_wsid = '{"BR": [12]}',
STATUS = '{"BR": {"12": 0}, "TH": { "12": 0}}',
arrange_style = '{"BR": {"12": 3}}' 
WHERE
	id IN (
		3290,
		3292,
		3300,
		3310,
		3312,
		3314,
		3315,
		3316,
		3317,
		3320,
		3321,
		3322,
		3323,
		3325,
		3326,
		3327,
		3328,
		3329,
		3330,
		3331,
		3336 
	);


--测试服更新
UPDATE `game_manage`
SET is_del = '{"BR": 0, "TH": 0, "VN": 0}'
WHERE game_code IN (
  'FC-EGAME-002', 'FC-EGAME-004', 'FC-EGAME-006', 'FC-EGAME-007', 'FC-EGAME-008',
  'FC-SLOT-001', 'FC-SLOT-002', 'FC-SLOT-004', 'FC-SLOT-006', 'FC-SLOT-008',
  'FC-SLOT-012', 'FC-SLOT-027', 'FC-SLOT-033', 'FC-SLOT-036', 'FC-SLOT-037', 'FC-SLOT-038',

  'PP-SLOT-100', 'PP-SLOT-262', 'PP-SLOT-333', 'PP-SLOT-441', 'PP-SLOT-453',
  'PP-SLOT-472', 'PP-SLOT-475', 'PP-SLOT-478', 'PP-SLOT-482', 'PP-SLOT-499',
  'PP-SLOT-508', 'PP-SLOT-524', 'PP-SLOT-525', 'PP-SLOT-548',
  'FC-SLOT-042', 'PP-SLOT-538', 'PP-SLOT-541', 'PP-SLOT-527',
  'FC-SLOT-044', 'FC-SLOT-045'
);
--测试服 netent的不上巴西地区
UPDATE `game_manage`
SET is_del = '{"BR": 1, "TH": 0, "VN": 0}'
WHERE game_code IN (
  'NETENT-SLOT-008', 'NETENT-SLOT-017', 'NETENT-SLOT-039', 'NETENT-SLOT-055',
  'NETENT-SLOT-082', 'NETENT-SLOT-094', 'NETENT-SLOT-116', 'NETENT-SLOT-137',
  'NETENT-SLOT-158', 'NETENT-SLOT-170'
);

--测试服 原有的游戏开通印度地区
UPDATE `live_m5`.`game_manage`
SET 
    is_del = JSON_OBJECT('IN', 0, 'BR', 0, 'TH', 0, 'VN', 0),
    support_currency = 'BRL,THB,VND,INR',
    support_country = 'BR,TH,VN,IN'
WHERE 
    integrator_code = 'AWC'
    AND JSON_EXTRACT(is_del, '$.BR') = 0
    AND JSON_EXTRACT(is_del, '$.VN') = 0
    AND JSON_EXTRACT(is_del, '$.TH') = 0;

--测试服 查询所有的巴西地区的fc游戏
SELECT
	JSON_EXTRACT( ext_name_ws, '$.BR' ) AS ext_name_ws2,
	game_code,
	ext_name,
	game_name,
	ext_name_ws,
	hot_country,
	sort AS old_sort,
	game_type_wsid,
	is_del,
	STATUS,
	game_type,
	display_name,
	icon,
	venue_code,
	support_currency,
	support_country,
	arrange_style,
	id,
	JSON_EXTRACT( sort, '$.BR.\"12\"' ) AS sort 
FROM
	`game_manage` 
WHERE
	JSON_EXTRACT( STATUS, '$.BR.\"12\"' ) = 0 
	AND JSON_EXTRACT( is_del, '$.BR' ) = 0 
	AND JSON_CONTAINS( game_type_wsid, '12', '$.BR' ) 
ORDER BY
	CAST( JSON_EXTRACT( sort, '$.BR.\"12\"' ) AS SIGNED INTEGER ) DESC,
	id DESC 
	LIMIT 50
	
	
		 UPDATE `live_m5`.`game_manage`
SET 
   is_del = '{"BR": 0, "TH": 0, "VN": 0}',
  support_currency = 'BRL,THB,VND',
  support_country = 'BR,TH,VN',

WHERE id =3326


SELECT id, game_code, game_name, ext_name, integrator_code, is_del
FROM `live_m5`.`game_manage`
WHERE (
    game_name LIKE '%FORTUNE GODDESS%' OR
    game_name LIKE '%GODS GRANT FORTUNE%' OR
    game_name LIKE '%BAO CHUAN FISHING%'
)
;

 UPDATE `live_m5`.`game_manage`
SET
  status = '{"VN": {"12": 0},"BR": {"2": 0, "11": 0, "13": 0}, "TH": {"1": 0, "2": 0}}',
   arrange_style = '{"BR": {"2": 3, "11": 3, "13": 3}}'
	WHERE id =3326


SELECT id,game_code,game_name,ext_name,integrator_code,is_del,support_currency,support_country FROM `live_m5`.`game_manage` WHERE id =3326


UPDATE `game_manage` SET
    `game_type_wsid` = '{"BR":[12]}',
    `venue_id` = 38,
    `venue_code` = 'FC',
    `integrator_code` = 'AWC',
    `support_country` = 'BR,TH,VN,IN',
    `support_currency` = 'BRL,THB,VND,INR',
    `arrange_style` = '{"BR":{"12":3}}',
    `sort` = '{"BR":{"12":0}}',
    `status` = '{"BR":{"12":1},"ID":{"1":0,"2":0},"TH":{"1":0,"2":0,"5":0,"8":0}}',
    `is_del` = '{"BR":0,"IN":0,"TH":0,"VN":0}',
    `ext_name` = 'awc_fc_slot_fcslot037_fortune_sheep',
    `ext_name_ws` = '{}',
WHERE `id` = 3334;




SELECT
	id,
	game_code,
	integrator_code,
	is_del,
	support_currency,
	support_country `game_type_wsid`,
	`venue_id`,
	`venue_code`,
	`integrator_code`,
	`support_country`,
	`support_currency`,
	`arrange_style`,
	`sort`,
	`status`,
	`ext_name_ws` ,
	game_name,
	ext_name
FROM
	`game_manage` 
WHERE
	id = 3326
	
	
 UPDATE `game_manage` SET
    `game_type_wsid` = '{"BR":[12]}',
    `support_country` = 'BR,TH,VN,IN',
    `support_currency` = 'BRL,THB,VND,INR',
    `arrange_style` = '{"BR":{"12":3}}',
    `sort` = '{"BR":{"12":0}}',
    `status` = '{"BR":{"12":1},"ID":{"12":1},"TH":{"12":1},"VN":{"12":1}}',
    `is_del` = '{"BR":0,"IN":0,"TH":0,"VN":0}'
WHERE (
    game_name LIKE '%TREASURE RAIDERS%'
    OR game_name LIKE '%Robin Hood%'
    OR game_name LIKE '%GLORY OF ROME%'
    OR game_name LIKE '%GRAND BLUE%'
    OR game_name LIKE '%RICH MAN%'
    OR game_name LIKE '%GOLD RUSH%'
    OR game_name LIKE '%MAGIC BEANS%'
    OR game_name LIKE '%WAR OF THE UNIVERSE%'
    OR game_name LIKE '%HOT POT PARTY%'
    OR game_name LIKE '%GODS GRANT FORTUNE%'
    OR game_name LIKE '%BAO CHUAN FISHING%'
    OR game_name LIKE '%FORTUNE MONEY BOOM%'
    OR game_name LIKE '%FORTUNE GODDESS%'
    OR game_name LIKE '%SUPER ELEMENTS%'
    OR game_name LIKE '%NIGHT MARKET 2%'
    OR game_name LIKE '%LEGEND OF INCA%'
    OR game_name LIKE '%FORTUNE EGG%'
    OR game_name LIKE '%WIN WIN NEKO%'
    OR game_name LIKE '%CIRCUS DOZER%'
    OR game_name LIKE '%MONEY TREE DOZER%'
    OR game_name LIKE '%ZEUS%'
    OR game_name LIKE '%CRAZY BUFFALO%'
    OR game_name LIKE '%GOLDEN GENIE%'
    OR game_name LIKE '%SUGAR BANG BANG%'
)
AND ext_name LIKE 'awc_fc%';


3296	FC-EGAME-007	MINES	awc_fc_egame_fcegame007_mines	AWC	{"BR": 0, "IN": 0, "TH": 0, "VN": 0}	{"BR": [1, 2, 12]}	BR,TH,VN,IN	BRL,THB,VND,INR	{"BR": {"1": 3, "2": 3, "8": 3, "12": 3}}	{"BR": {"1": 0, "2": 0, "8": 0, "12": 0}}	{"BR": {"1": 0, "2": 0, "8": 0, "12": 0}, "TH": {"4": 0, "5": 0}}
3296	FC-EGAME-007	MINES	awc_fc_egame_fcegame007_mines	AWC	{"BR": 0, "IN": 0, "TH": 0, "VN": 0}	{"BR": [12]}	BR,TH,VN,IN	BRL,THB,VND,INR	{"BR": {"1": 3, "2": 3, "8": 3, "12": 3}}	{"BR": {"1": 0, "2": 0, "8": 0, "12": 0}}	{"BR": {"1": 0, "2": 0, "8": 0, "12": 0}, "TH": {"4": 0, "5": 0}}



--测试服 更新所有的pp 游戏上架
UPDATE `game_manage` SET
    `game_type_wsid` = '{"BR":[11]}',
    `support_country` = 'BR,TH,VN,IN',
    `support_currency` = 'BRL,THB,VND,INR',
    `arrange_style` = '{"BR":{"11":3}}',
    `sort` = '{"BR":{"11":0}}',
    `status` = '{"BR":{"11":1},"ID":{"11":1},"TH":{"11":1},"VN":{"11":1}}',
    `is_del` = '{"BR":0,"IN":0,"TH":0,"VN":0}'
WHERE 
    `game_code` LIKE '%PP-%'
    AND `integrator_code` = 'AWC'
    AND (`icon` = '' OR `icon` IS NULL)
    AND (
        `game_type_wsid` != '{"BR":[11]}' OR
        `support_country` != 'BR,TH,VN,IN' OR
        `support_currency` != 'BRL,THB,VND,INR' OR
        `arrange_style` != '{"BR":{"11":3}}' OR
        `sort` != '{"BR":{"11":0}}' OR
        `status` != '{"BR":{"11":1},"ID":{"11":1},"TH":{"11":1},"VN":{"11":1}}' OR
        `is_del` != '{"BR":0,"IN":0,"TH":0,"VN":0}'
    );



SELECT
	game_code,
	ext_name,
	game_name,
	ext_name_ws,
	hot_country,
	sort AS old_sort,
	game_type_wsid,
	is_del,
	STATUS,
game_type,
	display_name,
	icon,
	venue_code,
	support_currency,
	support_country,
	arrange_style,
	id,
	JSON_EXTRACT( sort, '$.VN.\"12\"' ) AS sort 
FROM
	`game_manage` 
WHERE
	JSON_EXTRACT( STATUS, '$.VN.\"12\"' ) = 0 
	AND JSON_EXTRACT( is_del, '$.VN' ) = 0 
	AND JSON_CONTAINS( game_type_wsid, '12', '$.VN' ) 
ORDER BY
	CAST( JSON_EXTRACT( sort, '$.VN.\"12\"' ) AS SIGNED INTEGER ) DESC,
	id DESC 
	LIMIT 50
	
	
	SELECT
	game_code,
	ext_name,
	game_name,
	ext_name_ws,
	hot_country,
	sort AS old_sort,
	game_type_wsid,
	is_del,
	STATUS,
	game_type,
	display_name,
	icon,
	venue_code,
	support_currency,
	support_country,
	arrange_style,
	id,
	JSON_EXTRACT( sort, '$.BR.\"12\"' ) AS sort 
FROM
	`game_manage` 
WHERE
	JSON_EXTRACT( STATUS, '$.BR.\"12\"' ) = 0 
	AND JSON_EXTRACT( is_del, '$.BR' ) = 0 
	AND JSON_CONTAINS( game_type_wsid, '12', '$.BR' ) 
ORDER BY
	CAST( JSON_EXTRACT( sort, '$.BR.\"12\"' ) AS SIGNED INTEGER ) DESC,
	id DESC 
	LIMIT 50
	
	
	UPDATE `game_manage` SET
    `game_type_wsid` = '{"BR": [12], "ID": [12], "TH": [12], "VN": [12]}'
WHERE (
    game_name LIKE '%TREASURE RAIDERS%'
    OR game_name LIKE '%Robin Hood%'
    OR game_name LIKE '%GLORY OF ROME%'
    OR game_name LIKE '%GRAND BLUE%'
    OR game_name LIKE '%RICH MAN%'
    OR game_name LIKE '%GOLD RUSH%'
    OR game_name LIKE '%MAGIC BEANS%'
    OR game_name LIKE '%WAR OF THE UNIVERSE%'
    OR game_name LIKE '%HOT POT PARTY%'
    OR game_name LIKE '%GODS GRANT FORTUNE%'
    OR game_name LIKE '%BAO CHUAN FISHING%'
    OR game_name LIKE '%FORTUNE MONEY BOOM%'
    OR game_name LIKE '%FORTUNE GODDESS%'
    OR game_name LIKE '%SUPER ELEMENTS%'
    OR game_name LIKE '%NIGHT MARKET 2%'
    OR game_name LIKE '%LEGEND OF INCA%'
    OR game_name LIKE '%FORTUNE EGG%'
    OR game_name LIKE '%WIN WIN NEKO%'
    OR game_name LIKE '%CIRCUS DOZER%'
    OR game_name LIKE '%MONEY TREE DOZER%'
    OR game_name LIKE '%ZEUS%'
    OR game_name LIKE '%CRAZY BUFFALO%'
    OR game_name LIKE '%GOLDEN GENIE%'
    OR game_name LIKE '%SUGAR BANG BANG%'
)
AND ext_name LIKE 'awc_fc%';


6956	1008	The Frog Prince	SLOT	0	{"BR": [13], "TH": [13]}	{"en": "The Frog Prince", "pt": "The Frog Prince", "th": "The Frog Prince", "vi": "The Frog Prince", "zh-CN": "The Frog Prince"}	file/game/1745310984227310172_kzQsDzDl.png	119	WinTo	WinTo	BR,TH,VN		USD,BRL,THB,VND	{"BR": {"2": 3, "11": 3, "13": 3}, "TH": {"13": 3}}	{"BR": {"2": 0, "11": 0, "13": 0}, "TH": {}}	{"BR": {"2": 0, "11": 0, "13": 0}, "ID": {"1": 0, "2": 0}, "TH": {"1": 0, "2": 0, "13": 0}}	{"BR": 0, "TH": 0, "VN": 0}	winto_winto_slot_1008_the_frog_prince	{}	2025-04-22 16:33:49	2025-04-28 18:39:04
6932	FC-SLOT-042	ROMA GLADIATRIX	SLOT	0	{}	{"en": "ROMA GLADIATRIX", "th": "วีรสตรีแห่งโรมัน", "zh": "帝国竞技场"}	icon_path_here	104	FC	AWC	BR,TH,VN,IN		BRL,THB,VND,INR	{}	{}	{}	{"BR": 0, "IN": 0, "TH": 0, "VN": 0}	awc_fc_slot56ROMA_GLADIATRIX	{}	2025-04-09 16:26:23	2025-05-01 12:13:28


-- 更新为 4国同步
UPDATE game_manage
SET is_del = '{"BR": 0, "IN": 0, "TH": 0, "VN": 0}'
WHERE
    JSON_EXTRACT(is_del, '$.BR') = 0
    AND JSON_EXTRACT(is_del, '$.TH') = 0
    AND JSON_EXTRACT(is_del, '$.VN') = 0
    AND (game_code LIKE "%PP-%" OR game_code LIKE "%FC-%");
	
	-- 更新为 4国同步 印度市场 上了 netent的 游戏
	UPDATE `live_m5`.`game_manage`
SET 
  support_currency = 'BR,TH,VN,IN',
  support_country = 'BRL,THB,VND,INR'
WHERE 
    integrator_code = 'AWC'
    AND JSON_EXTRACT(is_del, '$.BR') = 0
    AND JSON_EXTRACT(is_del, '$.VN') = 0
		AND JSON_EXTRACT(is_del, '$.IN') = 0
		and support_country ='BR,TH,VN'
		AND support_currency='BRL,THB,VND'
    AND JSON_EXTRACT(is_del, '$.TH') = 0;
		
	-- 更新为 印度市场 默认添加为fc 分类
UPDATE game_manage
SET arrange_style = '{"BR": {"12": 3}, "IN": {"12": 3, "13": 3}}' , game_type_wsid ='{"BR": [12], "IN": [12]}'
WHERE game_code IN (
    'FC-SLOT-033',
    'FC-SLOT-032',
    'FC-SLOT-031',
    'FC-SLOT-030',
    'FC-SLOT-029',
    'FC-SLOT-028',
    'FC-SLOT-026',
    'FC-SLOT-025',
    'FC-SLOT-024',
    'FC-SLOT-023',
    'FC-SLOT-020',
    'FC-SLOT-019',
    'FC-SLOT-018',
    'FC-SLOT-017',
    'FC-SLOT-015',
    'FC-SLOT-013',
    'FC-SLOT-003',
    'FC-EGAME-003',
    'FC-EGAME-001'
);

		
		
		UPDATE `live_m5`.`game_manage`
SET 
  support_currency = 'BRL,THB,VND,INR',
  support_country = 'BR,TH,VN,IN'
WHERE 
    integrator_code = 'AWC'
    AND JSON_EXTRACT(is_del, '$.BR') = 0
    AND JSON_EXTRACT(is_del, '$.VN') = 0
  AND JSON_EXTRACT(is_del, '$.IN') = 0
    AND JSON_EXTRACT(is_del, '$.TH') = 0;
		
		
		SELECT
	id,
	game_code,
	game_type_wsid,
	`status`,
	arrange_style,
	is_del 
FROM
	game_manage 
WHERE


-- 更新分类到pp
UPDATE `live_m5`.`game_manage` 
SET 
`game_type_wsid` = '{"BR": [11], "IN": [11], "TH": [11], "VN": [11]}',
`status` = '{"BR": {"1": 0, "11": 0}, "ID": {"1": 0, "2": 0}, "IN": {"11": 0}, "TH": {"1": 0, "2": 0, "11": 0}, "VN": {"11": 0, "13": 0}}',
`arrange_style` = '{"BR": {"1": 3, "5": 3, "11": 3}, "IN": {"11": 3}, "TH": {"11": 3}, "VN": {"11": 3}}'
WHERE
	integrator_code = 'AWC' 
	AND support_currency = 'BRL,THB,VND,INR' 
	AND support_country = 'BR,TH,VN,IN' 
	AND venue_code = 'PP' 
	AND JSON_EXTRACT( is_del, '$.BR' ) = 0 
	AND JSON_EXTRACT( is_del, '$.VN' ) = 0 
	AND JSON_EXTRACT( is_del, '$.IN' ) = 0 
	AND JSON_EXTRACT( is_del, '$.TH' ) = 0;
	
	UPDATE `live_m5`.`game_manage` 
SET 
`game_type_wsid` = '{"BR": [12], "IN": [12], "TH": [12], "VN": [12]}',
`status` = '{"BR": {"12": 0}, "ID": {"12": 1}, "IN": {"12": 0}, "TH": {"12": 0}, "VN": {"12": 0}}',
`arrange_style` = '{"BR": {"12": 3}, "IN": {"12": 3, "13": 3}, "TH": {"12": 3}, "VN": {"12": 3}}'
WHERE
	integrator_code = 'AWC' 
	AND support_currency = 'BRL,THB,VND,INR' 
	AND support_country = 'BR,TH,VN,IN' 
	AND venue_code = 'FC' 
	AND JSON_EXTRACT( is_del, '$.BR' ) = 0 
	AND JSON_EXTRACT( is_del, '$.VN' ) = 0 
	AND JSON_EXTRACT( is_del, '$.IN' ) = 0 
	AND JSON_EXTRACT( is_del, '$.TH' ) = 0;
	
	
	
	
		
------测试更新过 图片的 游戏
	UPDATE game_manage
SET icon = CASE game_code
    WHEN 'PP-SLOT-100' THEN 'file/game/1742010198753528850_EqPYVXZj.png'
    WHEN 'PP-SLOT-333' THEN 'file/game/1742009286841078070_hPJEHQIC.png'
    WHEN 'PP-SLOT-453' THEN 'file/game/1742009233824206055_RfgHYYyE.png'
    WHEN 'PP-SLOT-472' THEN 'file/game/1742008768288998242_YdgoAUeu.png'
    WHEN 'PP-SLOT-475' THEN 'file/game/1742008267286004763_yhYqgoci.png'
    WHEN 'PP-SLOT-478' THEN 'file/game/1742008224951812064_oWIFJyZb.png'
    WHEN 'PP-SLOT-482' THEN 'file/game/1742008170639285075_ZCJqytAJ.png'
    WHEN 'PP-SLOT-508' THEN 'file/game/1742008046490848499_xorvBWpp.png'
    WHEN 'PP-SLOT-524' THEN 'file/game/1742007983694398683_ZZpIamHP.png'
    WHEN 'PP-SLOT-525' THEN 'file/game/1742007907353775446_SQQmFKvA.png'
    WHEN 'PP-SLOT-548' THEN 'file/game/1742007540513350461_oQzdIamw.png'
    WHEN 'PP-SLOT-538' THEN 'file/game/1745551822389925746_AoFNlGgl.png'
    WHEN 'PP-SLOT-541' THEN 'file/game/1745551842748757486_gBYjvIoJ.png'
    WHEN 'PP-SLOT-527' THEN 'file/game/1745551853922675442_wEiGROAl.png'
    ELSE icon
END
WHERE game_code IN (
    'PP-SLOT-100', 'PP-SLOT-333', 'PP-SLOT-453', 'PP-SLOT-472', 
    'PP-SLOT-475', 'PP-SLOT-478', 'PP-SLOT-482', 'PP-SLOT-508', 
    'PP-SLOT-524', 'PP-SLOT-525', 'PP-SLOT-548', 'PP-SLOT-538', 
    'PP-SLOT-541', 'PP-SLOT-527'
)  and integrator_code = 'AWC' 
	AND support_currency = 'BRL,THB,VND,INR' 
	AND support_country = 'BR,TH,VN,IN' 
	AND venue_code = 'pp' 
	AND JSON_EXTRACT( is_del, '$.BR' ) = 0 
	AND JSON_EXTRACT( is_del, '$.VN' ) = 0 
	AND JSON_EXTRACT( is_del, '$.IN' ) = 0 
	AND JSON_EXTRACT( is_del, '$.TH' ) = 0;
	
	------pp. 不上  EGAME 类型
	UPDATE game_manage
SET 
    `status` = '{}',
    is_del = '{}',
    arrange_style = '{}',
    support_country = '{}',
    support_currency = '{}'
WHERE
    game_code LIKE '%PP-EGAME%'
    AND integrator_code = 'AWC';
  
	---- 插入 新加游戏 pp 
INSERT INTO `game_manage` (
	`game_code`,
	`game_name`,
	`game_type`,
	`game_type_id`,
	`game_type_wsid`,
	`display_name`,
	`venue_id`,
	`venue_code`,
	`integrator_code`,
	`support_country`,
	`support_currency`,
	`arrange_style`,
	`sort`,
	`status`,
	`is_del`,
	`ext_name`,
	`ext_name_ws`,
	icon
)
VALUES
('PP-SLOT-552', 'The Dog House – Royal Hunt', 'SLOT', 0, '{\"BR\": [11]}',
 '{\"en\": \"The Dog House – Royal Hunt\", \"pt\": \"The Dog House – Royal Hunt\", \"th\": \"บ้านหมา - โรยัล ฮันท์\", \"vi\": \"The Dog House - Royal Hunt\", \"zh-CN\": \"汪汪之家 – 皇家狩猎\"}',
 104, 'PP', 'AWC', 'BR,TH,VN,IN', 'BRL,THB,VND,INR',
 '{\"BR\": {\"11\": 3}}', '{\"BR\": {\"11\": 0}}', '{\"BR\": {\"11\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pp_slot552The_Dog_House_Royal_Hunt', '{}', ''),

('PP-SLOT-553', 'Triple Pot Gold', 'SLOT', 0, '{\"BR\": [11]}',
 '{\"en\": \"Triple Pot Gold\", \"pt\": \"Triple Pot Gold\", \"th\": \"3 หม้อทองคำ\", \"vi\": \"Triple Pot Gold\", \"zh-CN\": \"三金壶\"}',
 104, 'PP', 'AWC', 'BR,TH,VN,IN', 'BRL,THB,VND,INR',
 '{\"BR\": {\"11\": 3}}', '{\"BR\": {\"11\": 0}}', '{\"BR\": {\"11\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pp_slot553Triple_Pot_Gold', '{}', ''),


('PP-SLOT-564', 'Sleeping Dragon', 'SLOT', 0, '{\"BR\": [11]}',
 '{\"en\": \"Sleeping Dragon\", \"pt\": \"Sleeping Dragon\", \"th\": \"มังกรขี้เซา\", \"vi\": \"Sleeping Dragon\", \"zh-CN\": \"沉睡之龙\"}',
 104, 'PP', 'AWC', 'BR,TH,VN,IN', 'BRL,THB,VND,INR',
 '{\"BR\": {\"11\": 3}}', '{\"BR\": {\"11\": 0}}', '{\"BR\": {\"11\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pp_slot564Sleeping_Dragon', '{}', '');
 
 
   
	---- 插入 新加游戏 pp 
 INSERT INTO `live_m5`.`game_manage` (
	`game_code`,
	`game_name`,
	`game_type`,
	`game_type_id`,
	`game_type_wsid`,
	`display_name`,
	`icon`,
	`venue_id`,
	`venue_code`,
	`integrator_code`,
	`support_country`,
	`hot_country`,
	`support_currency`,
	`arrange_style`,
	`sort`,
	`status`,
	`is_del`,
	`ext_name`,
	`ext_name_ws`,
)
VALUES
	(
		6946,
		'PP-SLOT-565',
		'Fiesta Fortune',
		'SLOT',
		0,
		'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [13]}',
		'{\"en\": \"Fiesta Fortune\", \"pt\": \"Fiesta Fortune\", \"th\": \"เทศกาลแห่งความสุข์\", \"vi\": \"Fiesta Fortune\", \"zh-CN\": \"富贵嘉年华\"}',
		'1',
		104,
		'PP',
		'AWC',
		'BR,TH,VN,IN',
		'',
		'BRL,THB,VND,INR',
		'{\"BR\": {\"11\": 3, \"13\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"13\": 3}}',
		'{\"BR\": {\"11\": 0}, \"IN\": {}, \"TH\": {}, \"VN\": {}}',
		'{\"BR\": {\"11\": 0, \"13\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"13\": 0}}',
		'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
		'awc_pp_slot565Fiesta_Fortune',
		'{}',
	);

PP	SLOT	PP-SLOT-540	Ancient Island Megaways™	远古岛Megaways™	ล่าขุมทรัพย์เกาะในตำนาน เมกาเวย์	Ancient Island Megaways™	アンシエント アイランド メガウェ	에이션트 아일랜드 메가웨이즈	96,55%	Very High	6		10000	-	Y	Y	2/10/25	2/11/25	Y	Y	-	
PP	SLOT	PP-SLOT-542	Savannah Legend	萨凡纳传奇	ตำนานซาวาน่า	Savannah Legend	サバンナ レジェンド	사바나 레전드	96,50%	Very High	5	1024	10000	-	Y	Y	2/10/25	2/11/25	Y	Y	-	
PP	SLOT	PP-SLOT-543	Bigger Bass Splash	超疯狂大鲈鱼溅水	บิ๊กเกอร์ บาส สแปลช	Bigger Bass Splash	ビッガー・バス・スプラッシュ	비거 베스 스플래시	96,50%	Very High	5	10	5000	-	Y	Y	2/14/25	2/17/25	Y	Y	-	
PP	SLOT	PP-SLOT-544	Big Bass Return to the Races	疯狂大鲈鱼重返竞赛	บิ๊ก บาสกลับสู่สนาม	Big Bass Return to the Races	ビッグ・バス・リターン・トゥ・ザ	빅 베스 리턴 투 더 레이스	96,07%	Very High	5	10	5000	-	Y	Y	2/27/25	2/28/25	Y	Y	-	
PP	SLOT	PP-SLOT-545	Peppe’s Pepperoni Pizza Plaza	意式腊香肠比萨	เปเป้ เปปโปโรนี่ พลาซ่า	Peppe's Pepperoni Pizza Plaza	ペッぺズ ペパロニ ピザ プラザ	페페의 페퍼로니 피자 플라자	96,55%	Very High	5	720	6000	-	Y	Y	2/20/25	2/21/25	Y	Y	-	
PP	SLOT	PP-SLOT-546	5 Lions Megaways™ 2	5金狮Megaways™ 2	5 มังกรทองล่าทรัพย์ เมกาเวย์	5 Lions Megaways™ 2	5 ライオン メガウェイズ™ 2	5 라이언즈 메가웨이즈™ 2	96,50%	Very High	6	262144	8000	-	Y	Y	2/27/25	2/28/25	Y	Y	-	
PP	SLOT	PP-SLOT-547	Greedy Fortune Pig	贪财猪	เจ้าหมูนำโชคจอมโลภ	Greedy Fortune Pig	グリーディー フォーチューン ピッグ	그리디 포춘 피그	96,50%		5	10	8888	-	Y	Y	3/19/25	3/20/25	Y	Y	-	
PP	SLOT	PP-SLOT-548	Raging Waterfall Megaways™	猛烈瀑布 Megaways	น้ำตกระห่ำ เมกาเวยส์	Raging Waterfall Megaways	レイジング・ウォーターフォール・メガウェイズ	레이징 워터폴 메가웨이즈	96,50%		6	20	10000	-	Y	Y	3/7/25	3/10/25	Y	Y	-	
PP	SLOT	PP-SLOT-549	Wild Wild Joker	超狂野小丑	ไวลด์ ไวลกด์ โจ๊กเกอร์	Wild Wild Joker	ワイルド ワイルド ジョーカー	와일드 와일드 조커	96,49%		5	20	5000	-	Y	Y	3/20/25	3/21/25	Y	Y	-	
PP	SLOT	PP-SLOT-550	Lucky's Wild Pub	疯狂好运酒吧	ลักกี้ไวลด์ผับ	Lucky's Wild Pub	ラッキーズ ワイルドパブ	럭키스 와일드 펍	96,50%		5	25	10000	-	Y	Y	3/13/25	3/14/25	Y	Y	-	


PP-SLOT-041 PP-SLOT-079 PP-SLOT-105 PP-SLOT-124 PP-SLOT-129 PP-SLOT-128 PP-SLOT-130 PP-SLOT-134 PP-SLOT-136  PP-SLOT-138
 PP-SLOT-142  PP-SLOT-140  PP-SLOT-145 PP-SLOT-146 PP-SLOT-147 PP-SLOT-148 PP-SLOT-149 PP-SLOT-150 PP-SLOT-151 PP-SLOT-152 PP-SLOT-153 PP-SLOT-154 PP-SLOT-155  PP-SLOT-157 PP-SLOT-158 PP-SLOT-159
  PP-SLOT-161
 
PP	SLOT	PP-SLOT-552	The Dog House – Royal Hunt	汪汪之家 – 皇家狩猎
PP	SLOT	PP-SLOT-553	Triple Pot Gold	三金壶
PP	SLOT	PP-SLOT-554	Book of Monsters	怪物之书
PP	SLOT	PP-SLOT-555	Blitz Super Wheel	疾风超级轮盘
PP	SLOT	PP-SLOT-556	Bandit Megaways™	暴徒Megaways
PP	SLOT	PP-SLOT-557	Joker’s Jewels Cash	小丑珠宝现金
PP	SLOT	PP-SLOT-558	Ride The Lightning 	驰骋闪电
PP	SLOT	PP-SLOT-559	5 Lions Reborn	5金狮重生
PP	SLOT	PP-SLOT-560	Big Bass Bonanza 1000	疯狂大鲈鱼 1000
PP	SLOT	PP-SLOT-561	Cash Surge	现金暴增
PP	SLOT	PP-SLOT-562	Gates of Olympus Super Scatter	奥林匹斯之门超神力
PP	SLOT	PP-SLOT-563	Witch Heart Megaways™	魔女之心Megaways
PP	SLOT	PP-SLOT-564	Sleeping Dragon	沉睡之龙
PP	SLOT	PP-SLOT-565	Fiesta Fortune	富贵嘉年华




INSERT INTO `live_m5`.`game_manage` (
	`game_code`,
	`game_name`,
	`game_type`,
	`game_type_id`,
	`game_type_wsid`,
	`display_name`,
	`icon`,
	`venue_id`,
	`venue_code`,
	`integrator_code`,
	`support_country`,
	`hot_country`,
	`support_currency`,
	`arrange_style`,
	`sort`,
	`status`,
	`is_del`,
	`ext_name`,
	`ext_name_ws`
)
VALUES
-- PP-SLOT-540
(
	'PP-SLOT-540',
	'Ancient Island Megaways™',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Ancient Island Megaways™\", \"zh-CN\": \"远古岛Megaways™\", \"th\": \"ล่าขุมทรัพย์เกาะในตำนาน เมกาเวย์\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot540Ancient_Island_Megaways',
	'{}'
),
-- PP-SLOT-542
(
	'PP-SLOT-542',
	'Savannah Legend',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Savannah Legend\", \"zh-CN\": \"萨凡纳传奇\", \"th\": \"ตำนานซาวาน่า\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot542Savannah_Legend',
	'{}'
),
-- PP-SLOT-543
(
	'PP-SLOT-543',
	'Bigger Bass Splash',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Bigger Bass Splash\", \"zh-CN\": \"超疯狂大鲈鱼溅水\", \"th\": \"บิ๊กเกอร์ บาส สแปลช\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot543Bigger_Bass_Splash',
	'{}'
),
-- PP-SLOT-544
(
	'PP-SLOT-544',
	'Big Bass Return to the Races',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Big Bass Return to the Races\", \"zh-CN\": \"疯狂大鲈鱼重返竞赛\", \"th\": \"บิ๊ก บาสกลับสู่สนาม\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot544Big_Bass_Return_to_the_Races',
	'{}'
),
-- PP-SLOT-545
(
	'PP-SLOT-545',
	'Peppe’s Pepperoni Pizza Plaza',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Peppe’s Pepperoni Pizza Plaza\", \"zh-CN\": \"意式腊香肠比萨\", \"th\": \"เปเป้ เปปโปโรนี่ พลาซ่า\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot545Peppe_Pizza_Plaza',
	'{}'
),
-- PP-SLOT-546
(
	'PP-SLOT-546',
	'5 Lions Megaways™ 2',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"5 Lions Megaways™ 2\", \"zh-CN\": \"5金狮Megaways™ 2\", \"th\": \"5 มังกรทองล่าทรัพย์ เมกาเวย์\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot5465_Lions_Megaways_2',
	'{}'
),
-- PP-SLOT-547
(
	'PP-SLOT-547',
	'Greedy Fortune Pig',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Greedy Fortune Pig\", \"zh-CN\": \"贪财猪\", \"th\": \"เจ้าหมูนำโชคจอมโลภ\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot547Greedy_Fortune_Pig',
	'{}'
),
-- PP-SLOT-548
(
	'PP-SLOT-548',
	'Raging Waterfall Megaways™',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Raging Waterfall Megaways™\", \"zh-CN\": \"猛烈瀑布 Megaways\", \"th\": \"น้ำตกระห่ำ เมกาเวยส์\"}',
	'file/game/1744616536968035686_XDyDiaBn.png',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot548Raging_Waterfall_Megaways',
	'{}'
),
-- PP-SLOT-549
(
	'PP-SLOT-549',
	'Wild Wild Joker',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Wild Wild Joker\", \"zh-CN\": \"超狂野小丑\", \"th\": \"ไวลด์ ไวลกด์ โจ๊กเกอร์\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot549Wild_Wild_Joker',
	'{}'
),
-- PP-SLOT-550
(
	'PP-SLOT-550',
	'Lucky\'s Wild Pub',
	'SLOT',
	5,
	'{\"BR\": [11], \"IN\": [11], \"TH\": [11], \"VN\": [11]}',
	'{\"en\": \"Lucky\'s Wild Pub\", \"zh-CN\": \"疯狂好运酒吧\", \"th\": \"ลักกี้ไวลด์ผับ\"}',
	'',
	'1',
	104,
	'PP',
	'BR,TH,VN,IN',
	'',
	'BRL,THB,VND,INR',
	'{\"BR\": {\"11\": 3}, \"IN\": {\"11\": 3}, \"TH\": {\"11\": 3}, \"VN\": {\"11\": 3}}',
	'{\"BR\": {\"11\": 0}, \"IN\": {\"11\": 0}, \"TH\": {\"11\": 0}, \"VN\": {\"11\": 0}}',
	'{\"BR\": {\"11\": 1}, \"IN\": {\"11\": 1}, \"TH\": {\"11\": 1}, \"VN\": {\"11\": 1}}',
	'{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
	'awc_pp_slot550Luckys_Wild_Pub',
	'{}'
);



PP	SLOT	PP-SLOT-530	Mining Rush	采矿热	ไมนิง รัช	Mining Rush	マイニング ラッシュ	마이닝 러쉬	96,56%		7	Cluster Pays 5+		-	Y	Y	2024/12/19 週四	2024/12/20 週五	Y	Y	-	
PP	SLOT	PP-SLOT-531	Aztec Smash	阿兹特克猛击	ทุบแอซเท็ก	Aztec Smash	アステカ スマッシュ	아즈텍 스매쉬	96,52%	Very High	7	Cluster Pays 5+	9000	-	Y	Y	1/2/25	1/3/25	Y	Y	-	
PP	SLOT	PP-SLOT-532	Fonzo’s Feline Fortunes	猫的丰盛财富	ฟอนโซล่าแห่งโชคลาภ	Fonzo's Feline Fortunes	フォンゾーズ フェライン フォーチ	폰조스 펠린 포춘스	96,50%	Very High	5	10	5000	-	Y	Y	1/9/25	1/10/25	Y	Y	-	
PP	SLOT	PP-SLOT-533	Floating Dragon – Year of the Snake	鱼跃龙门 – 喜迎蛇年	มังกรเหินฟ้า ปีมะเส็ง	Floating Dragon - Year of the Snake	フローティング ドラゴン - イヤー	플로팅 드래곤 - 뱀의 해	96,71%	Very High	5	10	5000	-	Y	Y	1/9/25	1/10/25	Y	Y	-	
PP	SLOT	PP-SLOT-534	Brick House Bonanza	财富堆砌	บ้านอิฐมหาโชค	Brick House Bonanza	ブリックハウス ボナンザ	브릭 하우스 보난자	96,50%	Very High	5	243	10000	-	Y	Y	1/9/25	1/10/25	Y	Y	-	
PP	SLOT	PP-SLOT-535	Aztec Gems Megaways™	古时代宝石 Megaways	อัญมณีแอซเท็ก เมก้าเวยส์	Aztec Gems Megaways™	アステカ ジェム メガウェイズ™	아즈텍 젬 메가웨이즈	96,58%	Low	3	512 Megaways	10000	-	Y	Y	1/20/25	1/21/25	Y	Y	-	
PP	SLOT	PP-SLOT-536	Wild Wild Pearls	狂暴珍珠	ไข่มุกมหาสมบัติ	Wild Wild Pearls	ワイルド ワイルド パール	와일드 와일드 펄스	96,46%	Very High	6	576	5000	-	Y	Y	1/20/25	1/21/25	Y	Y	-	
PP	SLOT	PP-SLOT-537	Irish Crown	爱尔兰王冠	มงกุฎแห่งไอร์แลนด์	Irish Crown	アイリッシュ クラウン	아이리시 크라운	96,52%	Medium	5	20	5000	-	Y	Y	1/27/25	1/28/25	Y	Y	-	
PP	SLOT	PP-SLOT-538	Wild Wildebeest Wins	狂野羚羊大胜	วิลเดอบีสต์ไวด์ ล่ารางวัล	Wild Wildebeest Wins	ワイルド ワイルドビースト ウィンズ	와일드 윌더비스트 윈	96,45%					-	Y	Y	2/3/25	2/4/25	Y	Y	-	
PP	SLOT	PP-SLOT-539	Escape the Pyramid - Fire & Ice	逃脱金字塔 – 火与冰	แหกพีระมิด น้ำแข็งพิโรธ	Escape the Pyramid - Fire & Ice	エスケープ ザ ピラミッド - ファイヤー＆アイス	피라미드 탈출 - 불과 얼음	96,50%					-	Y	Y	2/3/25	2/4/25	Y	Y	-	



INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1007', 'Snow White', 'SLOT', 0, '{\"BR\": [13]}', '{\"en\": \"Snow White\", \"pt\": \"Snow White\", \"th\": \"Snow White\", \"vi\": \"Snow White\", \"zh-CN\": \"Snow White\"}', 'file/game/1745217413067120456_TyxrZORZ.png', 119, 'WinTo', 'WinTo', 'BR,TH', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0}}', '{\"BR\": 0, \"TH\": 0}', 'winto_winto_slot_1007_snow_White', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1001', 'Panda Fortune Quest', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Panda Fortune Quest\", \"pt\": \"Panda Fortune Quest\", \"th\": \"Panda Fortune Quest\", \"vi\": \"Panda Fortune Quest\", \"zh-CN\": \"Panda Fortune Quest\"}', 'file/game/1745310385334236727_gHucVUrh.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {}, \"VN\": {}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1001_panda_fortune_quest', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1002', 'Dragon Koi Legends', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Dragon Koi Legends\", \"pt\": \"Dragon Koi Legends\", \"th\": \"Dragon Koi Legends\", \"vi\": \"Dragon Koi Legends\", \"zh-CN\": \"Dragon Koi Legends\"}', 'file/game/1745310456951706552_qPwVetjE.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {}, \"VN\": {}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1002_dragon_koi_legends', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1003', 'Legend of the Monkey Kingdom', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Legend of the Monkey Kingdom\", \"pt\": \"Legend of the Monkey Kingdom\", \"th\": \"Legend of the Monkey Kingdom\", \"vi\": \"Legend of the Monkey Kingdom\", \"zh-CN\": \"Legend of the Monkey Kingdom\"}', 'file/game/1745310509331559398_KgsROunb.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {}, \"VN\": {}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1003_legend_monkey_kingdom', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1004', 'Savage Legend', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Savage Legend\", \"pt\": \"Savage Legend\", \"th\": \"Savage Legend\", \"vi\": \"Savage Legend\", \"zh-CN\": \"Savage Legend\"}', 'file/game/1745310546129214445_ySNLnpZn.jpg', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {}, \"VN\": {}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1004_savage_legend', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1005', 'Phoenix And Treasure', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Phoenix And Treasure\", \"pt\": \"Phoenix And Treasure\", \"th\": \"Phoenix And Treasure\", \"vi\": \"Phoenix And Treasure\", \"zh-CN\": \"Phoenix And Treasure\"}', 'file/game/1745310581874963310_lgTQHxfY.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {}, \"VN\": {}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1005_phoenix_treasure', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1006', 'Bar romance', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Bar romance\", \"pt\": \"Bar romance\", \"th\": \"Bar romance\", \"vi\": \"Bar romance\", \"zh-CN\": \"Bar romance\"}', 'file/game/1745310619033863443_wnyiNzZE.jpg', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {}, \"VN\": {}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1006_bar_romance', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1008', 'The Frog Prince', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"The Frog Prince\", \"pt\": \"The Frog Prince\", \"th\": \"The Frog Prince\", \"vi\": \"The Frog Prince\", \"zh-CN\": \"The Frog Prince\"}', 'file/game/1745310984227310172_kzQsDzDl.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {}, \"VN\": {}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1008_the_frog_prince', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1009', 'Mermaid Legend', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Mermaid Legend\", \"pt\": \"Mermaid Legend\", \"th\": \"Mermaid Legend\", \"vi\": \"Mermaid Legend\", \"zh-CN\": \"Mermaid Legend\"}', 'file/game/1746529304770351574_qcTKYhqI.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {}, \"VN\": {}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1009_Mermaid_Legend', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1010', 'Hal and the Wings of Dawn', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Hal and the Wings of Dawn\", \"pt\": \"Hal and the Wings of Dawn\", \"th\": \"Hal and the Wings of Dawn\", \"vi\": \"Hal and the Wings of Dawn\", \"zh-CN\": \"Hal and the Wings of Dawn\"}', 'file/game/1746529337407004155_JAsQiUTz.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {\"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 1}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 1}, \"VN\": {\"13\": 1}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1010_Hal_and_the_Wings_of_Dawn', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1011', 'Moonlight City', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Moonlight City\", \"pt\": \"Moonlight City\", \"th\": \"Moonlight City\", \"vi\": \"Moonlight City\", \"zh-CN\": \"Moonlight City\"}', 'file/game/1746529361163356321_LAJkDeaT.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {\"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 1}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 1}, \"VN\": {\"13\": 1}}', '{\"BR\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1011_Moonlight_City', '{}');
INSERT INTO `game_manage` ( `game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`) VALUES ('1012', 'Dragon Ball-Treasure of the Dragon', 'SLOT', 0, '{\"BR\": [13], \"TH\": [13], \"VN\": [13]}', '{\"en\": \"Dragon Ball\", \"pt\": \"Dragon Ball\", \"th\": \"Dragon Ball\", \"vi\": \"Dragon Ball\", \"zh-CN\": \"Dragon Ball\"}', 'file/game/1745310984227310172_kzQsDzDl.png', 119, 'WinTo', 'WinTo', 'BR,TH,VN', '', 'USD,BRL,THB,VND', '{\"BR\": {\"2\": 3, \"11\": 3, \"13\": 3}, \"TH\": {\"13\": 3}, \"VN\": {\"13\": 3}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 0}, \"TH\": {\"13\": 0}, \"VN\": {\"13\": 0}}', '{\"BR\": {\"2\": 0, \"11\": 0, \"13\": 1}, \"ID\": {\"1\": 0, \"2\": 0}, \"TH\": {\"1\": 0, \"2\": 0, \"13\": 1}, \"VN\": {\"13\": 1}}', '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'winto_winto_slot_1012_Dragon_Ball_Treasure_of_the_Dragon', '{}');



SELECT * FROM `game_category` WHERE is_del = 0 ORDER BY id DESC LIMIT 10
SELECT * FROM `game_venue` WHERE game_venue.code = 'JILI' ORDER BY id DESC LIMIT 10



---- 更新现有的 JILI 游戏
UPDATE `live_m5`.`game_manage` 
	SET 
	`game_type_wsid` = '{\"BR\": [10], \"IN\": [10], \"TH\": [10], \"VN\": [10]}',
	`icon` = '1',
	`venue_id` = 44,
	`venue_code` = 'JILI',
	`integrator_code` = 'AWC',
	`support_country` = 'BR,TH,VN,IN',
	`support_currency` = 'BRL,THB,VND,INR',
	`arrange_style` = '{\"BR\": {\"10\": 3}, \"VN\": {\"10\": 3}}',
	`status` = '{\"BR\": {\"10\": 0}, \"IN\": {\"10\": 0}, \"TH\": {\"10\": 0}, \"VN\": {\"10\": 0}}',
	`is_del` = '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}'
WHERE
	game_code like 'JILI-SLOT%';
	
	---- 更新现有的 JDB 游戏
	UPDATE `live_m5`.`game_manage` 
	SET 
	`game_type_wsid` = '{\"BR\": [14], \"IN\": [14], \"TH\": [14], \"VN\": [14]}',
	`icon` = '1',
	`support_country` = 'BR,TH,VN,IN',
	`support_currency` = 'BRL,THB,VND,INR',
	arrange_style ='{"BR": {"14": 3}, "IN": {"14": 3}, "TH": {"14": 3}, "VN": {"14": 3}}',
	sort='{"BR": {"14": 0}, "IN": {"14": 0}, "TH": {"14": 0}, "VN": {"14": 0}}',
	

	`status` = '{\"BR\": {\"14\": 0}, \"IN\": {\"14\": 0}, \"TH\": {\"14\": 0}, \"VN\": {\"14\": 0}}',
	`is_del` = '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}'
WHERE
	game_code like 'JDB%' and id>3426;
	
		---- 添加新的pg 游戏
	INSERT INTO `live_m5`.`game_manage`
(`game_code`, `game_name`, `game_type`, `game_type_id`, `game_type_wsid`, `display_name`, `icon`, `venue_id`, `venue_code`, `integrator_code`, `support_country`, `hot_country`, `support_currency`, `arrange_style`, `sort`, `status`, `is_del`, `ext_name`, `ext_name_ws`, `created_at`, `updated_at`)
VALUES
-- PG-SLOT-150
('PG-SLOT-150', 'Geisha''s Revenge', 'SLOT', 0, '{\"BR\": [4], \"IN\": [4], \"TH\": [4], \"VN\": [4]}',
 '{\"en\": \"Geisha''s Revenge\"}', '1', 52, 'PG', 'AWC', 'BR,TH,VN,IN', '', 'BRL,THB,VND,INR',
 '{\"BR\": {\"4\": 3}, \"IN\": {\"4\": 3}, \"TH\": {\"4\": 3}, \"VN\": {\"4\": 3}}',
 '{\"BR\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": {\"4\": 0}, \"ID\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pg_slot_pgslot150_geishas_revenge', '{}', now(), now()),

-- PG-SLOT-151
('PG-SLOT-151', 'Fortune Snake', 'SLOT', 0, '{\"BR\": [4], \"IN\": [4], \"TH\": [4], \"VN\": [4]}',
 '{\"en\": \"Fortune Snake\"}', '1', 52, 'PG', 'AWC', 'BR,TH,VN,IN', '', 'BRL,THB,VND,INR',
 '{\"BR\": {\"4\": 3}, \"IN\": {\"4\": 3}, \"TH\": {\"4\": 3}, \"VN\": {\"4\": 3}}',
 '{\"BR\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": {\"4\": 0}, \"ID\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pg_slot_pgslot151_fortune_snake', '{}', now(), now()),

-- PG-SLOT-152
('PG-SLOT-152', 'Incan Wonders', 'SLOT', 0, '{\"BR\": [4], \"IN\": [4], \"TH\": [4], \"VN\": [4]}',
 '{\"en\": \"Incan Wonders\"}', '1', 52, 'PG', 'AWC', 'BR,TH,VN,IN', '', 'BRL,THB,VND,INR',
 '{\"BR\": {\"4\": 3}, \"IN\": {\"4\": 3}, \"TH\": {\"4\": 3}, \"VN\": {\"4\": 3}}',
 '{\"BR\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": {\"4\": 0}, \"ID\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pg_slot_pgslot152_incan_wonders', '{}', now(), now()),

-- PG-SLOT-153
('PG-SLOT-153', 'Mr. Treasure’s Fortune', 'SLOT', 0, '{\"BR\": [4], \"IN\": [4], \"TH\": [4], \"VN\": [4]}',
 '{\"en\": \"Mr. Treasure’s Fortune\"}', '1', 52, 'PG', 'AWC', 'BR,TH,VN,IN', '', 'BRL,THB,VND,INR',
 '{\"BR\": {\"4\": 3}, \"IN\": {\"4\": 3}, \"TH\": {\"4\": 3}, \"VN\": {\"4\": 3}}',
 '{\"BR\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": {\"4\": 0}, \"ID\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pg_slot_pgslot153_mr_treasures_fortune', '{}', now(), now()),

-- PG-SLOT-154
('PG-SLOT-154', 'Graffiti Rush', 'SLOT', 0, '{\"BR\": [4], \"IN\": [4], \"TH\": [4], \"VN\": [4]}',
 '{\"en\": \"Graffiti Rush\"}', '1', 52, 'PG', 'AWC', 'BR,TH,VN,IN', '', 'BRL,THB,VND,INR',
 '{\"BR\": {\"4\": 3}, \"IN\": {\"4\": 3}, \"TH\": {\"4\": 3}, \"VN\": {\"4\": 3}}',
 '{\"BR\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": {\"4\": 0}, \"ID\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pg_slot_pgslot154_graffiti_rush', '{}', now(), now()),

-- PG-SLOT-155
('PG-SLOT-155', 'Doomsday Rampage', 'SLOT', 0, '{\"BR\": [4], \"IN\": [4], \"TH\": [4], \"VN\": [4]}',
 '{\"en\": \"Doomsday Rampage\"}', '1', 52, 'PG', 'AWC', 'BR,TH,VN,IN', '', 'BRL,THB,VND,INR',
 '{\"BR\": {\"4\": 3}, \"IN\": {\"4\": 3}, \"TH\": {\"4\": 3}, \"VN\": {\"4\": 3}}',
 '{\"BR\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": {\"4\": 0}, \"ID\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pg_slot_pgslot155_doomsday_rampage', '{}', now(), now()),

-- PG-SLOT-156
('PG-SLOT-156', 'Knockout Riches', 'SLOT', 0, '{\"BR\": [4], \"IN\": [4], \"TH\": [4], \"VN\": [4]}',
 '{\"en\": \"Knockout Riches\"}', '1', 52, 'PG', 'AWC', 'BR,TH,VN,IN', '', 'BRL,THB,VND,INR',
 '{\"BR\": {\"4\": 3}, \"IN\": {\"4\": 3}, \"TH\": {\"4\": 3}, \"VN\": {\"4\": 3}}',
 '{\"BR\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": {\"4\": 0}, \"ID\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pg_slot_pgslot156_knockout_riches', '{}', now(), now()),

-- PG-SLOT-157
('PG-SLOT-157', 'Dead Man''s Riches', 'SLOT', 0, '{\"BR\": [4], \"IN\": [4], \"TH\": [4], \"VN\": [4]}',
 '{\"en\": \"Dead Man''s Riches\"}', '1', 52, 'PG', 'AWC', 'BR,TH,VN,IN', '', 'BRL,THB,VND,INR',
 '{\"BR\": {\"4\": 3}, \"IN\": {\"4\": 3}, \"TH\": {\"4\": 3}, \"VN\": {\"4\": 3}}',
 '{\"BR\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": {\"4\": 0}, \"ID\": {\"4\": 0}, \"IN\": {\"4\": 0}, \"TH\": {\"4\": 0}, \"VN\": {\"4\": 0}}',
 '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}', 'awc_pg_slot_pgslot157_dead_mans_riches', '{}', now(), now());
 
 -- 修改用户步骤
 --INSERT INTO `live_m5`.`site_user_auth` (`user_id`, `area_code`, `mobile`, `email`, `password`) VALUES (8880077, '91', '', 'game3g@qq.com', '$2a$10$eF/ErWdQaG7BcYM4nQs12.nScSccSCwBbzIrQELkfMPzW59hfOR4O');
--INSERT INTO `live_m5`.`site_user_wallet` (`user_id`, `balance`, `freeze`, `diamond`, `settlement_diamond`, `withdraw_diamond`, `withdraw_date`, `freeze_diamond`, `flow_amount`, `accumulation_diamond`, `level_up_exp`, `total_recharge`, `total_bet`, `first_amount`, `trc20_address`, `trc20_private_key`, `usdt_address`, `usdt_type`, `created_at`, `updated_at`) VALUES ( 8880077,200, 0.0000, 0, 0, 0, '1970-01-01', 0, 0.0000, 0, 0, 200.0000, 455.8483, 200.0000, '', '', '', '', NOW(),NOW());
-- 查询游戏分类
SELECT * FROM `game_manage` WHERE support_country LIKE '%BR%' AND is_del->'$.BR' = 0 AND game_manage.venue_code = 'E1SPORT' ORDER BY id DESC LIMIT 10;
SELECT * FROM `game_manage` WHERE support_country LIKE '%BR%' AND is_del->'$.BR' = 0 AND game_manage.venue_code = 'FC' ORDER BY id DESC LIMIT 10;
SELECT * FROM `game_manage` WHERE support_country LIKE '%BR%' AND JSON_CONTAINS(game_type_wsid->'$.BR', '13') AND is_del->'$.BR' = 0 ORDER BY id DESC LIMIT 10;
SELECT * FROM `game_manage` WHERE support_country LIKE '%BR%' AND is_del->'$.BR' = 0 AND game_manage.venue_code = 'JDB' ORDER BY id DESC LIMIT 10


 -- 增加印度利西亚市场游戏
UPDATE `live_m5`.`game_manage`
SET 
    `is_del` = '{"BR":0,"IN":0,"TH":0,"ID":0,"VN":0}',
    support_currency = 'BRL,THB,VND,INR,IDR',
    support_country = 'BR,TH,VN,IN,ID'
WHERE 
    integrator_code = 'AWC'
		and game_code LIKE 'FC-SLOT-%'
		AND support_country = 'BR,TH,VN,IN'
		AND support_currency = 'BRL,THB,VND,INR'
    AND JSON_EXTRACT(is_del, '$.BR') = 0
    AND JSON_EXTRACT(is_del, '$.VN') = 0
    AND JSON_EXTRACT(is_del, '$.IN') = 0
    AND JSON_EXTRACT(is_del, '$.TH') = 0;
		
		
		UPDATE `live_m5`.`game_manage`
SET 
  `game_type_wsid` = '{"BR": [12], "ID": [12], "IN": [12], "TH": [12], "VN": [12]}',
	`arrange_style` = '{"BR": {"12": 3}, "ID": {"12": 3}, "IN": {"12": 3, "13": 3}, "TH": {"12": 3}, "VN": {"12": 3}}',
	`status` = '{"BR": {"12": 0}, "ID": {"12": 0}, "IN": {"12": 0}, "TH": {"12": 0}, "VN": {"12": 0}}',
	sort = '{"BR": {"12": 0}, "ID": {"12": 0}, "IN": {"12": 45}, "TH": {}, "VN": {}}'
WHERE 
    integrator_code = 'AWC'
		and game_code LIKE 'FC-SLOT-%'
		AND support_country = 'BR,TH,VN,IN,ID'
		AND support_currency = 'BRL,THB,VND,INR,IDR'
    AND JSON_EXTRACT(is_del, '$.BR') = 0
    AND JSON_EXTRACT(is_del, '$.VN') = 0
    AND JSON_EXTRACT(is_del, '$.IN') = 0
		AND JSON_EXTRACT(is_del, '$.ID') = 0
    AND JSON_EXTRACT(is_del, '$.TH') = 0;


---这里能不能加个判断 如果cn 存在则使用cn,如果zh-CN存在 则使用 zh-CN 否则使用en
UPDATE game_manage
SET display_name = JSON_SET(
    display_name,
    '$."zh-CN"', JSON_UNQUOTE(
        IF(
            JSON_CONTAINS_PATH(display_name, 'one', '$.cn'),
            JSON_EXTRACT(display_name, '$.cn'),
            IF(
                JSON_CONTAINS_PATH(display_name, 'one', '$."zh-CN"'),
                JSON_EXTRACT(display_name, '$."zh-CN"'),
                JSON_EXTRACT(display_name, '$.en')
            )
        )
    ),
    '$."zh-TW"', JSON_UNQUOTE(
        IF(
            JSON_CONTAINS_PATH(display_name, 'one', '$.cn'),
            JSON_EXTRACT(display_name, '$.cn'),
            IF(
                JSON_CONTAINS_PATH(display_name, 'one', '$."zh-CN"'),
                JSON_EXTRACT(display_name, '$."zh-CN"'),
                JSON_EXTRACT(display_name, '$.en')
            )
        )
    )
)
WHERE support_country = 'BR,TH,VN,IN,ID'
  AND game_code = 'PP-SLOT-570';
	
	
	UPDATE game_manage
SET display_name = JSON_SET(
    display_name,
    '$."id"', JSON_UNQUOTE(JSON_EXTRACT(display_name, '$.en'))
)
WHERE support_country = 'BR,TH,VN,IN,ID';

-- 更新基础表
UPDATE `live_m5`.`game_category` 
SET 
`i18n_json` = '{\"en\": \"JDB\", \"id\": \"JDB\", \"pt\": \"JDB\", \"th\": \"JDB\", \"vi\": \"JDB\", \"zh-CN\": \"JDB\"}',
`support_country` = 'BR,VN,TH,ID,IN',
`sort` = '{\"BR\": 20, \"ID\": 11, \"IN\": 20, \"TH\": 20, \"VN\": 20}',
`status` = '{\"BR\": 0, \"ID\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
`is_del` = 0
WHERE
	`name` = 'JDB';
	
	
	SELECT * FROM `game_category`  WHERE `name` LIKE 'JILI' OR `name` LIKE 'JDB'
	
	
-- 更新基础表
UPDATE `live_m5`.`game_category` 
SET 
`i18n_json` = '{\"en\": \"JILI\", \"pt\": \"JILI\", \"th\": \"JILI\", \"vi\": \"JILI\", \"zh-CN\": \"JILI\"}',
`support_country` = 'BR,VN,IN,TH',
`sort` = '{\"BR\": 3, \"IN\": 4, \"TH\": 5, \"VN\": 3}',
`status` = '{\"BR\": 0, \"IN\": 0, \"TH\": 0, \"VN\": 0}',
`is_del` = 0
WHERE
	`name` = 'JILI';
	
	
	
INSERT INTO `live_m5`.`game_integrator` (`id`, `agent_code`, `agent_key`, `agent_api_key`, `code`, `name`, `login_url`, `lobby_url`, `default_url`, `game_list_url`, `product_list_url`, `game_brand_list_url`, `callback_url`, `icon`, `status`, `is_del`, `created_at`, `updated_at`) VALUES (16, 'YVYPXP', 'wsymHw99Bu3RQrBx1JX3O47rU71s6NYt', 'winto20250402', 'WinTo', 'WinTo', 'https://test-admin.okyslot.com/', 'http://34.92.56.178:8009/', 'http://34.92.56.178:40001/', 'https://test-admin.okyslot.com', 'http://34.92.56.178:8000/', 'http://34.92.56.178:8009/', 'https://devlive-api.sadpekiti.com/api/game/callback/winto', '', 0, 0, '2025-04-02 19:33:08', '2025-05-06 19:49:25');


-- 字段名存在于 game627dev 但不存在于 game627
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'game627dev' AND TABLE_NAME = 'game_manage'
AND COLUMN_NAME NOT IN (
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'game627' AND TABLE_NAME = 'game_manage'
);
--获取字段列表（包含所有字段）
SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY ORDINAL_POSITION SEPARATOR ', ')
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'game627dev'
  AND TABLE_NAME = 'game_manage';
	
--用老表的数据 插入新表	
INSERT INTO `game627`.`game_manage` ( game_code, game_name, game_type, game_type_id, game_type_wsid, display_name, icon, venue_id, venue_code, integrator_code, support_country, hot_country, support_currency, arrange_style, sort, status, is_del, ext_name, ext_name_ws, created_at, updated_at)  -- 列出字段，排除 id
SELECT  game_code, game_name, game_type, game_type_id, game_type_wsid, display_name, icon, venue_id, venue_code, integrator_code, support_country, hot_country, support_currency, arrange_style, sort, status, is_del, ext_name, ext_name_ws, created_at, updated_at  -- 与上面字段顺序一致
FROM `game627dev`.`game_manage`
WHERE game_code LIKE 'PG-SLOT-%';
