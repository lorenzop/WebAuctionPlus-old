<?php
	function itemAllowed ($itemId, $itemDamage)
	{
		switch ($itemId)
		{
			//Uncomment below to ban stone, dirt and cobblestone being sold.
			/* 
			case 1:
			case 3:
			case 4:
				return false;
				break;
			*/
			default: 
				return true; 
				break;
		}
	}

	function isTrueDamage ($itemId, $itemDamage)
	{
		$baseDur = 0;
		switch ($itemId)
		{
			case 261:
				$baseDur = 385;
				break;
			case 256:
			case 257:
			case 258:
			case 267:
			case 292:				
				$baseDur = 251;
				break;
			case 268:
			case 269:
			case 270:
			case 271:
			case 290:				
				$baseDur = 60;
				break;
			case 272:
			case 273:
			case 274:
			case 275:
			case 291:				
				$baseDur = 132;
				break;
			case 276:
			case 277:
			case 278:
			case 279:
			case 293:				
				$baseDur = 1562;
				break;
			case 283:
			case 284:
			case 285:
			case 286:
			case 294:				
				$baseDur = 33;
				break;
			case 298:				
				$baseDur = 34;
				break;
			case 299:				
				$baseDur = 49;
				break;
			case 300:				
				$baseDur = 46;
				break;	
			case 301:				
				$baseDur = 40;
				break;
			case 302:				
				$baseDur = 67;
				break;
			case 303:				
				$baseDur = 96;
				break;
			case 304:				
				$baseDur = 92;
				break;
			case 305:				
				$baseDur = 79;
				break;
			case 306:				
				$baseDur = 136;
				break;
			case 307:				
				$baseDur = 192;
				break;
			case 308:				
				$baseDur = 184;
				break;
			case 309:				
				$baseDur = 160;
				break;
			case 310:				
				$baseDur = 272;
				break;
			case 311:				
				$baseDur = 384;
				break;
			case 312:				
				$baseDur = 368;
				break;
			case 313:				
				$baseDur = 320;
				break;
			case 314:				
				$baseDur = 68;
				break;
			case 315:				
				$baseDur = 996;
				break;
			case 316:				
				$baseDur = 92;
				break;
			case 317:				
				$baseDur = 80;
				break;
			default:
				$baseDur = 0;
				break;
		}
		return $baseDur;
	}
	function getMarketPrice($itemTableId, $tableId)
	{	
		$table = "";
		switch ($tableId)
		{
			case 0:
				$table = "WA_Items";
				break;
			case 1:
				$table = "WA_Auctions";
				break;
			case 2:
				$table = "WA_Mail";
				break;
			case 3:
				$table = "WA_SellPrice";
				break;
		}
		$queryItem = mysql_query("SELECT * FROM $table WHERE id='$itemTableId'");
		$itemRow = mysql_fetch_row($queryItem);
		$itemId = $itemRow[1];
		$itemDamage = $itemRow[2];
		$foundIt = false;
		$queryMarket = "";
		//return $itemId;
		$queryEnchantLinks = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$itemTableId' AND itemTableId = '$tableId'");
		//return mysql_num_rows($queryEnchantLinks);
		$itemEnchantsArray = array ();
		
		while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinks))
		{  
			$itemEnchantsArray[] = $enchIdt;
			
		}
		$queryEnchantLinksMarket = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemTableId = '4'");
		
		$base = isTrueDamage($itemId, $itemDamage);
		if ($base > 0){
			if (mysql_num_rows($queryEnchantLinks) == 0){
				$queryMarket1=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='0' ORDER BY id DESC");
				$maxId = -1;
				$foundIt = false;
				//echo "first";
				while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1))
				{
					$queryMarket2 = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'");
					if (mysql_num_rows($queryMarket2)== 0){
						if ($idm > $maxId){
							$maxId = $idm;
							$foundIt = true;
						}
					}
				}
				if ($foundIt){
					$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE id = '$maxId' ORDER BY id DESC");
					$foundIt = true;
				}
			}else{
				$queryMarket1=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='0' ORDER BY id DESC");
				$maxId = -1;
				$foundIt = false;
				//echo "second";
				while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1))
				{
					$marketEnchantsArray = array ();
					$queryMarket2 = mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'");
					while(list($enchIdt)= mysql_fetch_row($queryMarket2))
					{
						if ($idm > $maxId){
							$marketEnchantsArray[] = $enchIdt;
							
						}
					}
					if((array_diff($itemEnchantsArray, $marketEnchantsArray) == null)&&(array_diff($marketEnchantsArray, $itemEnchantsArray) == null)){
						$maxId = $idm;
						$foundIt = true;
					}
					//print_r($itemEnchantsArray);
				}
				if ($foundIt){
					$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE id = '$maxId' ORDER BY id DESC");
					$foundIt = true;
				}
			}
		}else{
			$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='$itemDamage' ORDER BY id DESC");
			$foundIt = true;
		}
		if ($foundIt==false){
			//market price not found
			//echo "cant find";
			return 0;
		}else{
			//found get first item
			$rowMarket = mysql_fetch_row($queryMarket);
			$marketId = $rowMarket[0];
			if ($base > 0){
				$marketPrice = ($rowMarket[4]/$base)*($base - $itemDamage);
			}else{
				$marketPrice = $rowMarket[4];
			}
			return round($marketPrice, 2);
			
		}
	}
	function getItemName($itemId, $itemDamage)
	{
		switch ($itemId)
		{
			case 1:
				return "Stone";
				break;
			case 2:
				return "Grass";
				break;
			case 3:
				return "Dirt";
				break;	
			case 4:
				return "Cobblestone";
				break;
			case 5:
				return "Wooden Plank";
				break;
			case 6:
				switch ($itemDamage)
				{
					case 1:
						return "Redwood Sapling";
						break;
					case 2:
						return "Birch Sapling";
						break;
					default:
						return "Sapling";
						break;	
				}
				break;
			case 7:
				return "Bedrock";
				break;
			case 8:
				return "Water";
				break;
			case 9:
				return "Stationary Water";
				break;
			case 10:
				return "Lava";
				break;
			case 11:
				return "Stationary Lava";
				break;
			case 12:
				return "Sand";
				break;
			case 13:
				return "Gravel";
				break;
			case 14:
				return "Gold Ore";
				break;
			case 15:
				return "Iron Ore";
				break;
			case 16:
				return "Coal Ore";
				break;
			case 17:
				switch ($itemDamage)
				{
					case 1:
						return "Redwood Log";
						break;
					case 2:
						return "Birchwood Log";
						break;
					default:
						return "Log";
						break;
				}
				break;
			case 18:
				switch ($itemDamage)
				{
					case 1:
						return "Redwood Leaves";
						break;
					case 2:
						return "Birchwood Leaves";
						break;
					case 5:
						return "Redwood Leaves";
						break;
					case 6:
						return "Birchwood Leaves";
						break;
					default:
						return "Leaves";
						break;
				}
				break;
			case 19:
				return "Sponge";
				break;
			case 20:
				return "Glass";
				break;
			case 21:
				return "Lapis Lazuli Ore";
				break;
			case 22:
				return "Lapis Lazuli Block";
				break;
			case 23:
				return "Dispenser";
				break;
			case 24:
				return "Sandstone";
				break;
			case 25:
				return "Note Block";
				break;
			case 26:
				return "Bed Block";
				break;
			case 27:
				return "Powered Rail";
				break;
			case 28:
				return "Detector Rail";
				break;
			case 29:
				return "Sticky Piston";
				break;
			case 30:
				return "Web";
				break;
			case 31:
				switch ($itemDamage)
				{
					case 1:
						return "Tall Grass";
						break;
					case 2:
						return "Live Shrub";
						break;
					default:
						return "Dead Shrub";
						break;
				}
				break;
			case 32:
				return "Dead Shrub";
				break;
			case 33:
				return "Piston";
				break;
			case 34:
				return "Piston Head";
				break;
			case 35:
				switch ($itemDamage)
				{
					case 1:
						return "Orange Wool";
						break;
					case 2:
						return "Magenta Wool";
						break;
					case 3:
						return "Light Blue Wool";
						break;
					case 4:
						return "Yellow Wool";
						break;
					case 5:
						return "Light Green Wool";
						break;
					case 6:
						return "Pink Wool";
						break;
					case 7:
						return "Grey Wool";
						break;
					case 8:
						return "Light Grey Wool";
						break;
					case 9:
						return "Cyan Wool";
						break;
					case 10:
						return "Purple Wool";
						break;
					case 11:
						return "Blue Wool";
						break;
					case 12:
						return "Brown Wool";
						break;
					case 13:
						return "Dark Green Wool";
						break;
					case 14:
						return "Red Wool";
						break;
					case 15:
						return "Black Wool";
						break;
					default:
						return "White Wool";
						break;
				}
				break;
			case 37:
				return "Dandelion";
				break;
			case 38:
				return "Rose";
				break;
			case 39:
				return "Brown Mushroom";
				break;
			case 40:
				return "Red Mushroom";
				break;
			case 41:
				return "Gold Block";
				break;
			case 42:
				return "Iron Block";
				break;
			case 43:
				switch ($itemDamage)
				{
					case 1:
						return "Double Sandstone Slab";
						break;
					case 2:
						return "Double Wooden Slab";
						break;
					case 3:
						return "Double Cobblestone Slab";
						break;
					default:
						return "Double Stone Slab";
						break;
				}
				break;
			case 44:
				switch ($itemDamage)
				{
					case 1:
						return "Sandstone Slab";
						break;
					case 2:
						return "Wooden Slab";
						break;
					case 3:
						return "Cobblestone Slab";
						break;
					default:
						return "Stone Slab";
						break;
				}
				break;
			case 45:
				return "Brick Block";
				break;
			case 46:
				return "TNT";
				break;
			case 47:
				return "Bookshelf";
				break;
			case 48:
				return "Mossy Cobblestone";
				break;
			case 49:
				return "Obsidian";
				break;
			case 50:
				return "Torch";
				break;
			case 51:
				return "Fire";
				break;
			case 52:
				return "Monster Spawner";
				break;
			case 53:
				return "Wooden Stairs";
				break;
			case 54:
				return "Chest";
				break;
			case 55:
				return "Redstone Wire";
				break;
			case 56:
				return "Diamond Ore";
				break;
			case 57:
				return "Diamond Block";
				break;
			case 58:
				return "Workbench";
				break;
			case 59:
				return "Crops";
				break;
			case 60:
				return "Soil";
				break;
			case 61:
				return "Furnace";
				break;
			case 62:
				return "Burning Furnace";
				break;
			case 63:
				return "Sign Post";
				break;
			case 64:
				return "Wooden Door Block";
				break;
			case 65:
				return "Ladder";
				break;
			case 66:
				return "Rails";
				break;
			case 67:
				return "Cobblestone Stairs";
				break;
			case 68:
				return "Wall Sign";
				break;
			case 69:
				return "Lever";
				break;
			case 70:
				return "Stone Pressure Plate";
				break;
			case 71:
				return "Iron Door Block";
				break;
			case 72:
				return "Wooden Pressure Plate";
				break;
			case 73:
				return "Redstone Ore";
				break;
			case 74:
				return "Glowing Redstone Ore";
				break;
			case 75:
				return "Redstone Torch";
				break;
			case 76:
				return "Redstone Torch";
				break;
			case 77:
				return "Stone Button";
				break;
			case 78:
				return "Snow";
				break;
			case 79:
				return "Ice";
				break;
			case 80:
				return "Snow Block";
				break;
			case 81:
				return "Cactus";
				break;
			case 82:
				return "Clay";
				break;
			case 83:
				return "Sugar Cane (Block)";
				break;
			case 84:
				return "Jukebox";
				break;
			case 85:
				return "Fence";
				break;
			case 86:
				return "Pumpkin";
				break;	
			case 87:
				return "Netherrack";
				break;
			case 88:
				return "Soul Sand";
				break;
			case 89:
				return "Glowstone";
				break;
			case 90:
				return "Portal";
				break;
			case 91:
				return "Jack-O-Lantern";
				break;
			case 92:
				return "Cake";
				break;
			case 93:
				return "Redstone Repeater";
				break;
			case 94:
				return "Redstone Repeater";
				break;
			case 95:
				return "Locked Chest";
				break;
			case 96:
				return "Trapdoor";
				break;
			case 97:
				return "Silverfish Stone";
				break;
			case 98:
				switch ($itemDamage)
				{
					case 1:
						return "Mossy Stone Brick";
						break;
					case 2:
						return "Cracked Stone Brick";
						break;
					default:
						return "Stone Brick";
						break;
				}
				break;
			case 99:
				return "Brown Mushroom Cap";
				break;
			case 100:
				return "Red Mushroom Cap";
				break;
			case 101:
				return "Iron Bars";
				break;
			case 102:
				return "Glass Pane";
				break;
			case 103:
				return "Melon";
				break;
			case 104:
				return "Pumpkin Stem";
				break;
			case 105:
				return "Melon Stem";
				break;
			case 106:
				return "Vines";
				break;
			case 107:
				return "Fence Gate";
				break;
			case 108:
				return "Brick Stairs";
				break;
			case 109:
				return "Stone Brick Stairs";
				break;
			case 110:
				return "Mycelium";
				break;
			case 111:
				return "Lily Pad";
				break;
			case 112:
				return "Nether Brick";
				break;
			case 113:
				return "Nether Brick Fence";
				break;
			case 114:
				return "Nether Brick Stairs";
				break;
			case 115:
				return "Nether Wart";
				break;
			case 116:
				return "Enchantment Table";
				break;
			case 117:
				return "Brewing Stand (Block)";
				break;
			case 118:
				return "Cauldron (Block)";
				break;
			case 119:
				return "End Portal";
				break;
			case 120:
				return "End Portal Frame";
				break;
			case 121:
				return "End Stone";
				break;
			case 122:
				return "Dragon Egg";
				break;
			case 256:
				return "Iron Shovel ".round(($itemDamage/251)*100, 1)."% damaged";
				break;
			case 257:
				return "Iron Pickaxe ".round(($itemDamage/251)*100, 1)."% damaged";
				break;
			case 258:
				return "Iron Axe ".round(($itemDamage/251)*100, 1)."% damaged";
				break;
			case 259:
				return "Flint and Steel";
				break;
			case 260:
				return "Apple";
				break;
			case 261:
				return "Bow ".round(($itemDamage/385)*100, 1)."% damaged";
				break;
			case 262:
				return "Arrow";
				break;
			case 263:
				switch ($itemDamage)
				{
					case 1:
						return "Charcoal";
						break;
					default:
						return "Coal";
						break;
				}
				break;
			case 264:
				return "Diamond";
				break;
			case 265:
				return "Iron Ingot";
				break;
			case 266:
				return "Gold Ingot";
				break;
			case 267:
				return "Iron Sword ".round(($itemDamage/251)*100, 1)."% damaged";
				break;
			case 268:
				return "Wooden Sword ".round(($itemDamage/60)*100, 1)."% damaged";
				break;
			case 269:
				return "Wooden Shovel ".round(($itemDamage/60)*100, 1)."% damaged";
				break;
			case 270:
				return "Wooden Pickaxe ".round(($itemDamage/60)*100, 1)."% damaged";
				break;
			case 271:
				return "Wooden Axe ".round(($itemDamage/60)*100, 1)."% damaged";
				break;
			case 272:
				return "Stone Sword ".round(($itemDamage/132)*100, 1)."% damaged";
				break;
			case 273:
				return "Stone Shovel ".round(($itemDamage/132)*100, 1)."% damaged";
				break;
			case 274:
				return "Stone Pickaxe ".round(($itemDamage/132)*100, 1)."% damaged";
				break;
			case 275:
				return "Stone Axe ".round(($itemDamage/132)*100, 1)."% damaged";
				break;
			case 276:
				return "Diamond Sword ".round(($itemDamage/1562)*100, 1)."% damaged";
				break;
			case 277:
				return "Diamond Shovel ".round(($itemDamage/1562)*100, 1)."% damaged";
				break;
			case 278:
				return "Diamond Pickaxe ".round(($itemDamage/1562)*100, 1)."% damaged";
				break;
			case 279:
				return "Diamond Axe ".round(($itemDamage/1562)*100, 1)."% damaged";
				break;
			case 280:
				return "Stick";
				break;
			case 281:
				return "Bowl";
				break;
			case 282:
				return "Mushroom Soup";
				break;
			case 283:
				return "Gold Sword ".round(($itemDamage/33)*100, 1)."% damaged";
				break;
			case 284:
				return "Gold Shovel ".round(($itemDamage/33)*100, 1)."% damaged";
				break;
			case 285:
				return "Gold Pickaxe ".round(($itemDamage/33)*100, 1)."% damaged";
				break;
			case 286:
				return "Gold Axe ".round(($itemDamage/33)*100, 1)."% damaged";
				break;
			case 287:
				return "String";
				break;
			case 288:
				return "Feather";
				break;
			case 289:
				return "Sulphur";
				break;
			case 290:
				return "Wooden Hoe ".round(($itemDamage/60)*100, 1)."% damaged";
				break;
			case 291:
				return "Stone Hoe ".round(($itemDamage/132)*100, 1)."% damaged";
				break;
			case 292:
				return "Iron Hoe ".round(($itemDamage/251)*100, 1)."% damaged";
				break;
			case 293:
				return "Diamond Hoe ".round(($itemDamage/1562)*100, 1)."% damaged";
				break;
			case 294:
				return "Gold Hoe ".round(($itemDamage/33)*100, 1)."% damaged";
				break;
			case 295:
				return "Seeds";
				break;
			case 296:
				return "Wheat";
				break;
			case 297:
				return "Bread";
				break;
			case 298:
				return "Leather Helmet ".round(($itemDamage/34)*100, 1)."% damaged";
				break;
			case 299:
				return "Leather Chestplate ".round(($itemDamage/49)*100, 1)."% damaged";
				break;
			case 300:
				return "Leather Leggings ".round(($itemDamage/46)*100, 1)."% damaged";
				break;
			case 301:
				return "Leather Boots ".round(($itemDamage/40)*100, 1)."% damaged";
				break;
			case 302:
				return "Chain Mail Helmet ".round(($itemDamage/67)*100, 1)."% damaged";
				break;
			case 303:
				return "Chain Mail Chestplate ".round(($itemDamage/96)*100, 1)."% damaged";
				break;
			case 304:
				return "Chain Mail Leggings ".round(($itemDamage/92)*100, 1)."% damaged";
				break;
			case 305:
				return "Chain Mail Boots ".round(($itemDamage/79)*100, 1)."% damaged";
				break;
			case 306:
				return "Iron Helmet ".round(($itemDamage/136)*100, 1)."% damaged";
				break;
			case 307:
				return "Iron Chestplate ".round(($itemDamage/192)*100, 1)."% damaged";
				break;
			case 308:
				return "Iron Leggings ".round(($itemDamage/184)*100, 1)."% damaged";
				break;
			case 309:
				return "Iron Boots ".round(($itemDamage/160)*100, 1)."% damaged";
				break;
			case 310:
				return "Diamond Helmet ".round(($itemDamage/272)*100, 1)."% damaged";
				break;
			case 311:
				return "Diamond Chestplate ".round(($itemDamage/384)*100, 1)."% damaged";
				break;
			case 312:
				return "Diamond Leggings ".round(($itemDamage/368)*100, 1)."% damaged";
				break;
			case 313:
				return "Diamond Boots ".round(($itemDamage/320)*100, 1)."% damaged";
				break;
			case 314:
				return "Gold Helmet ".round(($itemDamage/68)*100, 1)."% damaged";
				break;
			case 315:
				return "Gold Chestplate ".round(($itemDamage/96)*100, 1)."% damaged";
				break;
			case 316:
				return "Gold Leggings ".round(($itemDamage/92)*100, 1)."% damaged";
				break;
			case 317:
				return "Gold Boots ".round(($itemDamage/80)*100, 1)."% damaged";
				break;
			case 318:
				return "Flint";
				break;
			case 319:
				return "Raw Porkchop";
				break;
			case 320:
				return "Cooked Porkchop";
				break;
			case 321:
				return "Painting";
				break;
			case 322:
				return "Golden Apple";
				break;
			case 323:
				return "Sign";
				break;
			case 324:
				return "Wooden Door";
				break;
			case 325:
				return "Bucket";
				break;
			case 326:
				return "Water Bucket";
				break;
			case 327:
				return "Lava Bucket";
				break;
			case 328:
				return "Minecart";
				break;
			case 329:
				return "Saddle";
				break;
			case 330:
				return "Iron Door";
				break;
			case 331:
				return "Redstone";
				break;
			case 332:
				return "Snowball";
				break;
			case 333:
				return "Boat";
				break;
			case 334:
				return "Leather";
				break;
			case 335:
				return "Milk Bucket";
				break;
			case 336:
				return "Clay Brick";
				break;
			case 337:
				return "Clay Balls";
				break;
			case 338:
				return "Sugar Cane";
				break;
			case 339:
				return "Paper";
				break;
			case 340:
				return "Book";
				break;
			case 341:
				return "Slimeball";
				break;
			case 342:
				return "Storage Minecart";
				break;
			case 343:
				return "Powered Minecart";
				break;
			case 344:
				return "Egg";
				break;
			case 345:
				return "Compass";
				break;
			case 346:
				return "Fishing Rod";
				break;
			case 347:
				return "Clock";
				break;
			case 348:
				return "Glowstone Dust";
				break;
			case 349:
				return "Raw Fish";
				break;
			case 350:
				return "Cooked Fish";
				break;
			case 351:
				switch ($itemDamage)
				{
					case 1:
						return "Rose Red";
						break;
					case 2:
						return "Cactus Green";
						break;
					case 3:
						return "Cocoa Beans";
						break;
					case 4:
						return "Lapis Lazuli";
						break;
					case 5:
						return "Purple Dye";
						break;
					case 6:
						return "Cyan Dye";
						break;
					case 7:
						return "Light Grey Dye";
						break;
					case 8:
						return "Grey Dye";
						break;
					case 9:
						return "Pink Dye";
						break;
					case 10:
						return "Lime Dye";
						break;
					case 11:
						return "Dandelion Yellow";
						break;
					case 12:
						return "Light Blue Dye";
						break;
					case 13:
						return "Magenta Dye";
						break;
					case 14:
						return "Orange Dye";
						break;
					case 15:
						return "Bone Meal";
						break;
					default:
						return "Ink Sack";
						break;
				}
				break;
			case 352:
				return "Bone";
				break;
			case 353:
				return "Sugar";
				break;
			case 354:
				return "Cake";
				break;
			case 355:
				return "Bed";
				break;
			case 356:
				return "Redstone Repeater";
				break;
			case 357:
				return "Cookie";
				break;
			case 358:
				return "Map ".$itemDamage;
				break;
			case 359:
				return "Shears";
				break;
			case 360:
				return "Melon Slice";
				break;
			case 361:
				return "Pumpkin Seeds";
				break;
			case 362:
				return "Melon Seeds";
				break;
			case 363:
				return "Raw Beef";
				break;
			case 364:
				return "Steak";
				break;
			case 365:
				return "Raw Chicken";
				break;
			case 366:
				return "Cooked Chicken";
				break;
			case 367:
				return "Rotten Flesh";
				break;
			case 368:
				return "Ender Pearl";
				break;
			case 369:
				return "Blaze Rod";
				break;
			case 370:
				return "Ghast Tear";
				break;
			case 371:
				return "Gold Nugget";
				break;
			case 372:
				return "Nether Wart";
				break;
			case 373:
				switch ($itemDamage)
				{
					case 0:
						return "Water Bottle";
						break;
					case 16:
						return "Awkward Potion";
						break;
					case 32:
						return "Thick Potion";
						break;
					case 64:
						return "Mundane Potion";
						break;
					case 8193:
						return "Regeneration Potion (0:45)";
						break;
					case 8194:
						return "Swiftness Potion (3:00)";
						break;
					case 8195:
						return "Fire Resistance Potion (3:00)";
						break;
					case 8196:
						return "Poison Potion (0:45)";
						break;
					case 8197:
						return "Healing Potion";
						break;
					case 8200:
						return "Weakness Potion (1:30)";
						break;
					case 8201:
						return "Strength Potion (3:00)";
						break;
					case 8202:
						return "Slowness Potion (1:30)";
						break;
					case 8204:
						return "Harming Potion";
						break;
					case 8225:
						return "Regeneration Potion II (0:22)";
						break;
					case 8226:
						return "Swiftness Potion II (1:30)";
						break;
					case 8228:
						return "Poison Potion II (0:22)";
						break;
					case 8229:
						return "Healing Potion II";
						break;
					case 8233:
						return "Strength Potion II (1:30)";
						break;
					case 8236:
						return "Harming Potion II";
						break;
					case 8257:
						return "Regeneration Potion (2:00)";
						break;
					case 8258:
						return "Swiftness Potion (8:00)";
						break;
					case 8259:
						return "Fire Resistance Potion (8:00)";
						break;
					case 8260:
						return "Poison Potion (2:00)";
						break;
					case 8264:
						return "Weakness Potion (4:00)";
						break;
					case 8265:
						return "Strength Potion (8:00)";
						break;
					case 8266:
						return "Slowness Potion (4:00)";
						break;
					case 16378:
						return "Fire Resistance Splash (2:15)";
						break;
					case 16385:
						return "Regeneration Splash (0:33)";
						break;
					case 16386:
						return "Swiftness Splash (2:15)";
						break;
					case 16388:
						return "Poison Splash (0:33)";
						break;
					case 16389:
						return "Healing Splash";
						break;
					case 16392:
						return "Weakness Splash (1:07)";
						break;
					case 16393:
						return "Strength Splash (2:15)";
						break;
					case 16394:
						return "Slowness Splash (2:15)";
						break;
					case 16396:
						return "Harming Splash";
						break;
					case 16418:
						return "Swiftness Splash II (1:07)";
						break;
					case 16420:
						return "Poison Splash II (0:16)";
						break;
					case 16421:
						return "Healing Splash II";
						break;
					case 16425:
						return "Strength Splash II (1:07)";
						break;
					case 16428:
						return "Harming Splash II";
						break;
					case 16449:
						return "Regeneration Splash (1:30)";
						break;
					case 16450:
						return "Swiftness Splash (6:00)";
						break;
					case 16451:
						return "Fire Resistance Splash (6:00)";
						break;
					case 16452:
						return "Poison Splash (1:30)";
						break;
					case 16456:
						return "Weakness Splash (3:00)";
						break;
					case 16457:
						return "Strength Splash (6:00)";
						break;
					case 16458:
						return "Slowness Splash (3:00)";
						break;
					case 16471:
						return "Regeneration Splash II (0:16)";
						break;
					default:
						return "Clear Potion";
						break;
				}
				break;
			case 374:
				return "Glass Bottle";
				break;
			case 375:
				return "Spider Eye";
				break;
			case 376:
				return "Fermented Spider Eye";
				break;
			case 377:
				return "Blaze Powder";
				break;
			case 378:
				return "Magma Cream";
				break;
			case 379:
				return "Brewing Stand";
				break;
			case 380:
				return "Cauldron";
				break;
			case 381:
				return "Eye of Ender";
				break;
			case 382:
				return "Glistering Melon (Slice)";
				break;
			case 2256:
				return "Music Disc (13)";
				break;
			case 2257:
				return "Music Disc (Cat)";
				break;
			case 2258:
				return "Music Disc (Blocks)";
				break;
			case 2259:
				return "Music Disc (Chirp)";
				break;
			case 2260:
				return "Music Disc (Far)";
				break;
			case 2261:
				return "Music Disc (Mall)";
				break;
			case 2262:
				return "Music Disc (Mellohi)";
				break;
			case 2263:
				return "Music Disc (Stal)";
				break;
			case 2264:
				return "Music Disc (Strad)";
				break;
			case 2265:
				return "Music Disc (Ward)";
				break;
			case 2266:
				return "Music Disc (11)";
				break;
			default:
				// TODO: Return "Unknown (id:value)" instead?
				return "air";
				break;		
		}
	}
	function getItemImage($itemId, $itemDamage)
	{
		switch ($itemId)
		{
			case 1:
				return "images/Grid_Stone.png";
				break;
			case 2:
				return "images/Grid_Grass.png";
				break;
			case 3:
				return "images/Grid_Dirt.png";
				break;	
			case 4:
				return "images/Grid_Cobblestone.png";
				break;
			case 5:
				return "images/Grid_Wooden_Plank.png";
				break;
			case 6:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Sapling_Spruce.png";
						break;
					case 2:
						return "images/Grid_Sapling_Birch.png";
						break;
					default:
						return "images/Grid_Sapling.png";
						break;	
				}
				break;
			case 7:
				return "images/Grid_Bedrock.png";
				break;
			case 8:
				return "images/Grid_Water.png";
				break;
			case 9:
				return "images/Grid_Water.png";
				break;
			case 10:
				return "images/Grid_Lava.png";
				break;
			case 11:
				return "images/Grid_Lava.png";
				break;
			case 12:
				return "images/Grid_Sand.png";
				break;
			case 13:
				return "images/Grid_Gravel.png";
				break;
			case 14:
				return "images/Grid_Gold_%28Ore%29.png";
				break;
			case 15:
				return "images/Grid_Iron_%28Ore%29.png";
				break;
			case 16:
				return "images/Grid_Coal_%28Ore%29.png";
				break;
			case 17:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Wood_%28Pine%29.png";
						break;
					case 2:
						return "images/Grid_Wood_%28Birch%29.png";
						break;
					default:
						return "images/Grid_Wood.png";
						break;
				}
				break;
			case 18:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Leaves_%28Pine%29.png";
						break;
					case 2:
						return "images/Grid_Leaves_%28Birch%29.png";
						break;
					case 5:
						return "images/Grid_Leaves_%28Pine%29.png";
						break;
					case 6:
						return "images/Grid_Leaves_%28Birch%29.png";
						break;
					default:
						return "images/Grid_Leaves.png";
						break;
				}
				break;
			case 19:
				return "images/Grid_Sponge.png";
				break;
			case 20:
				return "images/Grid_Glass.png";
				break;
			case 21:
				return "images/Grid_Lapis_Lazuli_%28Ore%29.png";
				break;
			case 22:
				return "images/Grid_Lapis_Lazuli_%28Block%29.png";
				break;
			case 23:
				return "images/Grid_Dispenser.png";
				break;
			case 24:
				return "images/Grid_Sandstone.png";
				break;
			case 25:
				return "images/Grid_Note_Block.png";
				break;
			case 26:
				return "images/Grid_Bed.png";
				break;
			case 27:
				return "images/Grid_Powered_Rail.png";
				break;
			case 28:
				return "images/Grid_Detector_Rail.png";
				break;
			case 29:
				return "images/Grid_Sticky_Piston.png";
				break;
			case 30:
				return "images/Grid_Cobweb.png";
				break;
			case 31:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Tall_Grass2.png";
						break;
					case 2:
						return "images/Grid_Tall_Grass3.png";
						break;
					default:
						return "images/Grid_Tall_Grass1.png";
						break;
				}
				break;
			case 32:
				return "images/Grid_Dead_Bush.png";
				break;
			case 33:
				return "images/Grid_Piston.png";
				break;
			case 34:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Sticky_Piston.png";
						break;
					default:
						return "images/Grid_Piston.png";
						break;
				}
				break;
			case 35:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Orange_Wool.png";
						break;
					case 2:
						return "images/Grid_Magenta_Wool.png";
						break;
					case 3:
						return "images/Grid_Light_Blue_Wool.png";
						break;
					case 4:
						return "images/Grid_Yellow_Wool.png";
						break;
					case 5:
						return "images/Grid_Lime_Wool.png";
						break;
					case 6:
						return "images/Grid_Pink_Wool.png";
						break;
					case 7:
						return "images/Grid_Gray_Wool.png";
						break;
					case 8:
						return "images/Grid_Light_Gray_Wool.png";
						break;
					case 9:
						return "images/Grid_Cyan_Wool.png";
						break;
					case 10:
						return "images/Grid_Purple_Wool.png";
						break;
					case 11:
						return "images/Grid_Blue_Wool.png";
						break;
					case 12:
						return "images/Grid_Brown_Wool.png";
						break;
					case 13:
						return "images/Grid_Green_Wool.png";
						break;
					case 14:
						return "images/Grid_Red_Wool.png";
						break;
					case 15:
						return "images/Grid_Black_Wool.png";
						break;
					default:
						return "images/Grid_White_Wool.png";
						break;
				}
				break;
			case 37:
				return "images/Grid_Dandelion.png";
				break;
			case 38:
				return "images/Grid_Rose.png";
				break;
			case 39:
				return "images/Grid_Brown_Mushroom.png";
				break;
			case 40:
				return "images/Grid_Red_Mushroom.png";
				break;
			case 41:
				return "images/Grid_Gold_%28Block%29.png";
				break;
			case 42:
				return "images/Grid_Iron_%28Block%29.png";
				break;
			case 43:
				// TODO: add double slab pictures
				switch ($itemDamage)
				{
					case 1:
						return "Double Sandstone Slab";
						break;
					case 2:
						return "Double Wooden Slab";
						break;
					case 3:
						return "Double Cobblestone Slab";
						break;
					default:
						return "images/Grid_Double_Stone_Slab.png";
						break;
				}
				break;
			case 44:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Sandstone_Slab.png";
						break;
					case 2:
						return "images/Grid_Wooden_Slab.png";
						break;
					case 3:
						return "images/Grid_Cobblestone_Slab.png";
						break;
					default:
						return "images/Grid_Stone_Slab.png";
						break;
				}
				break;
			case 45:
				return "images/Grid_Brick_%28Block%29.png";
				break;
			case 46:
				return "images/Grid_TNT.png";
				break;
			case 47:
				return "images/Grid_Bookshelf.png";
				break;
			case 48:
				return "images/Grid_Moss_Stone.png";
				break;
			case 49:
				return "images/Grid_Obsidian.png";
				break;
			case 50:
				return "images/Grid_Torch.png";
				break;
			case 51:
				return "images/Grid_Fire.png";
				break;
			case 52:
				return "images/Grid_Monster_Spawner.png";
				break;
			case 53:
				return "images/Grid_Wooden_Stairs.png";
				break;
			case 54:
				return "images/Grid_Chest.png";
				break;
			case 55:
				return "images/Grid_Redstone_%28Dust%29.png";
				break;
			case 56:
				return "images/Grid_Diamond_%28Ore%29.png";
				break;
			case 57:
				return "images/Grid_Diamond_%28Block%29.png";
				break;
			case 58:
				return "images/Grid_Crafting_Table.png";
				break;
			case 59:
				// TODO: add crop picture(s)
				return "Crops";
				break;
			case 60:
				return "images/Grid_Farmland.png"; // Soil
				break;
			case 61:
				return "images/Grid_Furnace.png";
				break;
			case 62:
				return "images/Grid_Furnace.png";
				break;
			case 63:
				return "images/Grid_Sign.png";
				break;
			case 64:
				return "images/Grid_Wooden_Door.png";
				break;
			case 65:
				return "images/Grid_Ladders.png";
				break;
			case 66:
				return "images/Grid_Rails.png";
				break;
			case 67:
				return "images/Grid_Cobblestone_Stairs.png";
				break;
			case 68:
				return "images/Grid_Sign.png";
				break;
			case 69:
				return "images/Grid_Lever.png";
				break;
			case 70:
				return "images/Grid_Stone_Pressure_Plate.png";
				break;
			case 71:
				return "images/Grid_Iron_Door.png";
				break;
			case 72:
				return "images/Grid_Wooden_Pressure_Plate.png";
				break;
			case 73:
				return "images/Grid_Redstone_%28Ore%29.png";
				break;
			case 74:
				return "images/Grid_Redstone_%28Ore%29.png";
				break;
			case 75:
				return "images/Grid_Redstone_%28Torch%29.png";
				break;
			case 76:
				return "images/Grid_Redstone_%28Torch%29.png";
				break;
			case 77:
				return "images/Grid_Stone_Button.png";
				break;
			case 78:
				return "images/Grid_Snow_%28Block%29.png";
				break;
			case 79:
				return "images/Grid_Ice.png";
				break;
			case 80:
				return "images/Grid_Snow_%28Block%29.png";
				break;
			case 81:
				return "images/Grid_Cactus.png";
				break;
			case 82:
				return "images/Grid_Clay_%28Block%29.png";
				break;
			case 83:
				return "images/Grid_Sugar_Cane.png";
				break;
			case 84:
				return "images/Grid_Jukebox.png";
				break;
			case 85:
				return "images/Grid_Fence.png";
				break;
			case 86:
				return "images/Grid_Pumpkin.png";
				break;	
			case 87:
				return "images/Grid_Netherrack.png";
				break;
			case 88:
				return "images/Grid_Soul_Sand.png";
				break;
			case 89:
				return "images/Grid_Glowstone_%28Block%29.png";
				break;
			case 90:
				return "images/Grid_Portal.png";
				break;
			case 91:
				return "images/Grid_Jack-O-Lantern.png";
				break;
			case 92:
				return "images/Grid_Cake.png";
				break;
			case 93:
				return "images/Grid_Redstone_%28Repeater%29.png";
				break;
			case 94:
				return "images/Grid_Redstone_%28Repeater%29.png";
				break;
			case 95:
				return "images/Grid_Chest.png"; // Locked Chest
				break;
			case 96:
				return "images/Grid_Trapdoor.png";
				break;
			case 97:
				return "images/Stone.png"; // Hidden Silverfish
				break;
			case 98:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Mossy_Stone_Brick.png";
						break;
					case 2:
						return "images/Grid_Cracked_Stone_Brick.png";
						break;
					default:
						return "images/Grid_Stone_Brick.png";
						break;
					}
				break;
			case 99:
				return "images/Grid_Brown_Mushroom.png";
				break;
			case 100:
				return "images/Grid_Red_Mushroom.png";
				break;
			case 101:
				return "images/Grid_Iron_Bars.png";
				break;
			case 102:
				return "images/Grid_Glass_Pane.png";
				break;
			case 103:
				return "images/Grid_Melon_%28Block%29.png";
				break;
			case 104:
				// TODO: add pumpkin and melon steam pictures
				return "Pumpkin Stem";
				break;
			case 105:
				return "Melon Stem";
				break;
			case 106:
				return "images/Grid_Vines.png";
				break;
			case 107:
				return "images/Grid_Fence_Gate.png";
				break;
			case 108:
				return "images/Grid_Brick_Stairs.png";
				break;
			case 109:
				return "images/Grid_Stone_Brick_Stairs.png";
				break;
			case 110:
				return "images/Grid_Mycelium.png";
				break;
			case 111:
				return "images/Grid_Lily_Pad.png";
				break;
			case 112:
				return "images/Grid_Nether_Brick.png";
				break;
			case 113:
				return "images/Grid_Nether_Brick_Fence.png";
				break;
			case 114:
				return "images/Grid_Nether_Brick_Stairs.png";
				break;
			case 115:
				return "images/Grid_Nether_Wart_Seeds.png";
				break;
			case 116:
				return "images/Grid_Enchantment_Table.png";
				break;
			case 117:
				return "images/Grid_Brewing_Stand.png";
				break;
			case 118:
				return "images/Grid_Cauldron.png";
				break;
			case 119:
				return "images/Grid_End_Portal.png";
				break;
			case 120:
				return "images/Grid_End_Portal_Frame.png";
				break;
			case 121:
				return "images/Grid_End_Stone.png";
				break;
			case 122:
				return "images/Grid_Dragon_Egg.png";
				break;
			case 256:
				return "images/Grid_Iron_Shovel.png";
				break;
			case 257:
				return "images/Grid_Iron_Pickaxe.png";
				break;
			case 258:
				return "images/Grid_Iron_Axe.png";
				break;
			case 259:
				return "images/Grid_Flint_and_Steel.png";
				break;
			case 260:
				return "images/Grid_Red_Apple.png";
				break;
			case 261:
				return "images/Grid_Bow.png";
				break;
			case 262:
				return "images/Grid_Arrow.png";
				break;
			case 263:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Charcoal_%28Item%29.png";
						break;
					default:
						return "images/Grid_Coal_%28Item%29.png";
						break;
				}
				break;
			case 264:
				return "images/Grid_Diamond_%28Gem%29.png";
				break;
			case 265:
				return "images/Grid_Iron_%28Ingot%29.png";
				break;
			case 266:
				return "images/Grid_Gold_%28Ingot%29.png";
				break;
			case 267:
				return "images/Grid_Iron_Sword.png";
				break;
			case 268:
				return "images/Grid_Wooden_Sword.png";
				break;
			case 269:
				return "images/Grid_Wooden_Shovel.png";
				break;
			case 270:
				return "images/Grid_Wooden_Pickaxe.png";
				break;
			case 271:
				return "images/Grid_Wooden_Axe.png";
				break;
			case 272:
				return "images/Grid_Stone_Sword.png";
				break;
			case 273:
				return "images/Grid_Stone_Shovel.png";
				break;
			case 274:
				return "images/Grid_Stone_Pickaxe.png";
				break;
			case 275:
				return "images/Grid_Stone_Axe.png";
				break;
			case 276:
				return "images/Grid_Diamond_Sword.png";
				break;
			case 277:
				return "images/Grid_Diamond_Shovel.png";
				break;
			case 278:
				return "images/Grid_Diamond_Pickaxe.png";
				break;
			case 279:
				return "images/Grid_Diamond_Axe.png";
				break;
			case 280:
				return "images/Grid_Stick.png";
				break;
			case 281:
				return "images/Grid_Bowl.png";
				break;
			case 282:
				return "images/Grid_Mushroom_Stew.png";
				break;
			case 283:
				return "images/Grid_Gold_Sword.png";
				break;
			case 284:
				return "images/Grid_Gold_Shovel.png";
				break;
			case 285:
				return "images/Grid_Gold_Pickaxe.png";
				break;
			case 286:
				return "images/Grid_Gold_Axe.png";
				break;
			case 287:
				return "images/Grid_String.png";
				break;
			case 288:
				return "images/Grid_Feather.png";
				break;
			case 289:
				return "images/Grid_Gunpowder.png";
				break;
			case 290:
				return "images/Grid_Wooden_Hoe.png";
				break;
			case 291:
				return "images/Grid_Stone_Hoe.png";
				break;
			case 292:
				return "images/Grid_Iron_Hoe.png";
				break;
			case 293:
				return "images/Grid_Diamond_Hoe.png";
				break;
			case 294:
				return "images/Grid_Gold_Hoe.png";
				break;
			case 295:
				return "images/Grid_Seeds.png";
				break;
			case 296:
				return "images/Grid_Wheat.png";
				break;
			case 297:
				return "images/Grid_Bread.png";
				break;
			case 298:
				return "images/Grid_Leather_Cap.png";
				break;
			case 299:
				return "images/Grid_Leather_Tunic.png";
				break;
			case 300:
				return "images/Grid_Leather_Pants.png";
				break;
			case 301:
				return "images/Grid_Leather_Boots.png";
				break;
			case 302:
				return "images/Grid_Chain_Armor_Helmet.png";
				break;
			case 303:
				return "images/Grid_Chain_Armor_Chestplate.png";
				break;
			case 304:
				return "images/Grid_Chain_Armor_Leggings.png";
				break;
			case 305:
				return "images/Grid_Chain_Armor_Boots.png";
				break;
			case 306:
				return "images/Grid_Iron_Helmet.png";
				break;
			case 307:
				return "images/Grid_Iron_Chestplate.png";
				break;
			case 308:
				return "images/Grid_Iron_Leggings.png";
				break;
			case 309:
				return "images/Grid_Iron_Boots.png";
				break;
			case 310:
				return "images/Grid_Diamond_Helmet.png";
				break;
			case 311:
				return "images/Grid_Diamond_Chestplate.png";
				break;
			case 312:
				return "images/Grid_Diamond_Leggings.png";
				break;
			case 313:
				return "images/Grid_Diamond_Boots.png";
				break;
			case 314:
				return "images/Grid_Gold_Helmet.png";
				break;
			case 315:
				return "images/Grid_Gold_Chestplate.png";
				break;
			case 316:
				return "images/Grid_Gold_Leggings.png";
				break;
			case 317:
				return "images/Grid_Gold_Boots.png";
				break;
			case 318:
				return "images/Grid_Flint.png";
				break;
			case 319:
				return "images/Grid_Raw_Porkchop.png";
				break;
			case 320:
				return "images/Grid_Cooked_Porkchop.png";
				break;
			case 321:
				return "images/Grid_Painting.png";
				break;
			case 322:
				return "images/Grid_Golden_Apple.png";
				break;
			case 323:
				return "images/Grid_Sign.png";
				break;
			case 324:
				return "images/Grid_Wooden_Door.png";
				break;
			case 325:
				return "images/Grid_Bucket.png";
				break;
			case 326:
				return "images/Grid_Water_Bucket.png";
				break;
			case 327:
				return "images/Grid_Lava_Bucket.png";
				break;
			case 328:
				return "images/Grid_Minecart.png";
				break;
			case 329:
				return "images/Grid_Saddle.png";
				break;
			case 330:
				return "images/Grid_Iron_Door.png";
				break;
			case 331:
				return "images/Grid_Redstone_%28Dust%29.png";
				break;
			case 332:
				return "images/Grid_Snowball.png";
				break;
			case 333:
				return "images/Grid_Boat.png";
				break;
			case 334:
				return "images/Grid_Leather.png";
				break;
			case 335:
				return "images/Grid_Milk_Bucket.png";
				break;
			case 336:
				return "images/Grid_Clay_%28Brick%29.png";
				break;
			case 337:
				return "images/Grid_Clay_%28Item%29.png";
				break;
			case 338:
				return "images/Grid_Sugar_Cane.png";
				break;
			case 339:
				return "images/Grid_Paper.png";
				break;
			case 340:
				return "images/Grid_Book.png";
				break;
			case 341:
				return "images/Grid_Slimeball.png";
				break;
			case 342:
				return "images/Grid_Minecart_with_Chest.png";
				break;
			case 343:
				return "images/Grid_Minecart_with_Furnace.png";
				break;
			case 344:
				return "images/Grid_Egg.png";
				break;
			case 345:
				return "images/Grid_Compass.png";
				break;
			case 346:
				return "images/Grid_Fishing_Rod.png";
				break;
			case 347:
				return "images/Grid_Clock.png";
				break;
			case 348:
				return "images/Grid_Glowstone_%28Dust%29.png";
				break;
			case 349:
				return "images/Grid_Raw_Fish.png";
				break;
			case 350:
				return "images/Grid_Cooked_Fish.png";
				break;
			case 351:
				switch ($itemDamage)
				{
					case 1:
						return "images/Grid_Rose_Red.png";
						break;
					case 2:
						return "images/Grid_Cactus_Green.png";
						break;
					case 3:
						return "images/Grid_Cocoa_Beans.png";
						break;
					case 4:
						return "images/Grid_Lapis_Lazuli_%28Dye%29.png";
						break;
					case 5:
						return "images/Grid_Purple_Dye.png";
						break;
					case 6:
						return "images/Grid_Cyan_Dye.png";
						break;
					case 7:
						return "images/Grid_Light_Gray_Dye.png";
						break;
					case 8:
						return "images/Grid_Gray_Dye.png";
						break;
					case 9:
						return "images/Grid_Pink_Dye.png";
						break;
					case 10:
						return "images/Grid_Lime_Dye.png";
						break;
					case 11:
						return "images/Grid_Dandelion_Yellow.png";
						break;
					case 12:
						return "images/Grid_Light_Blue_Dye.png";
						break;
					case 13:
						return "images/Grid_Magenta_Dye.png";
						break;
					case 14:
						return "images/Grid_Orange_Dye.png";
						break;
					case 15:
						return "images/Grid_Bone_Meal.png";
						break;
					default:
						return "images/Grid_Ink_Sac.png";
						break;
				}
				break;
			case 352:
				return "images/Grid_Bone.png";
				break;
			case 353:
				return "images/Grid_Sugar.png";
				break;
			case 354:
				return "images/Grid_Cake.png";
				break;
			case 355:
				return "images/Grid_Bed.png";
				break;
			case 356:
				return "images/Grid_Redstone_%28Repeater%29.png";
				break;
			case 357:
				return "images/Grid_Cookie.png";
				break;
			case 358:
				return "images/Grid_Map_%28Item%29.png";
				break;
			case 359:
				return "images/Grid_Shears.png";
				break;
			case 360:
				return "images/Grid_Melon_%28Slice%29.png";
				break;
			case 361:
				return "images/Grid_Pumpkin_Seeds.png";
				break;
			case 362:
				return "images/Grid_Melon_Seeds.png";
				break;
			case 363:
				return "images/Grid_Raw_Beef.png";
				break;
			case 364:
				return "images/Grid_Steak.png";
				break;
			case 365:
				return "images/Grid_Raw_Chicken.png";
				break;
			case 366:
				return "images/Grid_Cooked_Chicken.png";
				break;
			case 367:
				return "images/Grid_Rotten_Flesh.png";
				break;
			case 368:
				return "images/Grid_Ender_Pearl.png";
				break;
			case 369:
				return "images/Grid_Blaze_Rod.png";
				break;
			case 370:
				return "images/Grid_Ghast_Tear.png";
				break;
			case 371:
				return "images/Grid_Gold_Nugget.png";
				break;
			case 372:
				return "images/Grid_Nether_Wart_Seeds.png";
				break;
			case 373:
				switch ($itemDamage)
				{
					case 0:
						return "images/Grid_Water_Bottle.png";
						break;
					case 16:
						return "images/Grid_Awkward_Potion.png";
						break;
					case 32:
						return "images/Grid_Thick_Potion.png";
						break;
					case 64:
						return "images/Grid_Mundane_Potion.png";
						break;
					case 8193:
						return "images/Grid_Potion_of_Regeneration.png";
						break;
					case 8194:
						return "images/Grid_Potion_of_Swiftness.png";
						break;
					case 8195:
						return "images/Grid_Potion_of_Fire_Resistance.png";
						break;
					case 8196:
						return "images/Grid_Potion_of_Poison.png";
						break;
					case 8197:
						return "images/Grid_Instant_Health.png";
						break;
					case 8200:
						return "images/Grid_Potion_of_Poison.png";
						break;
					case 8201:
						return "images/Grid_Potion_of_Strength.png";
						break;
					case 8202:
						return "images/Grid_Potion_of_Slowness.png";
						break;
					case 8204:
						return "images/Grid_Potion_of_Harming.png";
						break;
					case 8225:
						return "images/Grid_Potion_of_Regeneration.png";
						break;
					case 8226:
						return "images/Grid_Potion_of_Swiftness.png";
						break;
					case 8228:
						return "images/Grid_Potion_of_Poison.png";
						break;
					case 8229:
						return "images/Grid_Instant_Health.png";
						break;
					case 8233:
						return "images/Grid_Potion_of_Strength.png";
						break;
					case 8236:
						return "images/Grid_Potion_of_Harming.png";
						break;
					case 8257:
						return "images/Grid_Potion_of_Regeneration.png";
						break;
					case 8258:
						return "images/Grid_Potion_of_Swiftness.png";
						break;
					case 8259:
						return "images/Grid_Potion_of_Fire_Resistance.png";
						break;
					case 8260:
						return "images/Grid_Potion_of_Poison.png";
						break;
					case 8264:
						return "images/Grid_Potion_of_Weakness.png";
						break;
					case 8265:
						return "images/Grid_Potion_of_Strength.png";
						break;
					case 8266:
						return "images/Grid_Potion_of_Slowness.png";
						break;
					case 16378:
						return "images/Grid_Fire_Resistance_Splash_Potion.png";
						break;
					case 16385:
						return "images/Grid_Regeneration_Splash_Potion.png";
						break;
					case 16386:
						return "images/Grid_Swiftness_Splash_Potion.png";
						break;
					case 16388:
						return "images/Grid_Poison_Splash_Potion.png";
						break;
					case 16389:
						return "images/Grid_Healing_Splash_Potion.png";
						break;
					case 16392:
						return "images/Grid_Weakness_Splash_Potion.png";
						break;
					case 16393:
						return "images/Grid_Strength_Splash_Potion.png";
						break;
					case 16394:
						return "images/Grid_Slowness_Splash_Potion.png";
						break;
					case 16396:
						return "images/Grid_Harming_Splash_Potion.png";
						break;
					case 16418:
						return "images/Grid_Swiftness_Splash_Potion.png";
						break;
					case 16420:
						return "images/Grid_Poison_Splash_Potion.png";
						break;
					case 16421:
						return "images/Grid_Healing_Splash_Potion.png";
						break;
					case 16425:
						return "images/Grid_Strength_Splash_Potion.png";
						break;
					case 16428:
						return "images/Grid_Harming_Splash_Potion.png";
						break;
					case 16449:
						return "images/Grid_Regneration_Splash_Potion.png";
						break;
					case 16450:
						return "images/Grid_Swiftness_Splash_Potion.png";
						break;
					case 16451:
						return "images/Grid_Fire_Resistance_Splash_Potion.png";
						break;
					case 16452:
						return "images/Grid_Poison_Splash_Potion.png";
						break;
					case 16456:
						return "images/Grid_Weakness_Splash_Potion.png";
						break;
					case 16457:
						return "images/Grid_Strength_Splash_Potion.png";
						break;
					case 16458:
						return "images/Grid_Slowness_Splash_Potion.png";
						break;
					case 16471:
						return "images/Grid_Regeneration_Splash_Potion.png";
						break;
					default:
						return "images/Grid_Water_Bottle.png";
						break;
				}
				break;
			case 374:
				return "images/Grid_Glass_Bottle.png";
				break;
			case 375:
				return "images/Grid_Spider_Eye.png";
				break;
			case 376:
				return "images/Grid_Fermented_Spider_Eye.png";
				break;
			case 377:
				return "images/Grid_Blaze_Powder.png";
				break;
			case 378:
				return "images/Grid_Magma_Cream.png";
				break;
			case 379:
				return "images/Grid_Brewing_Stand.png";
				break;
			case 380:
				return "images/Grid_Cauldron.png";
				break;
			case 381:
				return "images/Grid_Eye_of_Ender.png";
				break;
			case 382:
				return "images/Grid_Glistering_Melon.png";
				break;
			case 2256:
				return "images/Grid_Gold_Disc.png";
				break;
			case 2257:
				return "images/Grid_Green_Disc.png";
				break;
			case 2258:
				return "images/Grid_Blocks_Disc.png";
				break;
			case 2259:
				return "images/Grid_Chirp_Disc.png";
				break;
			case 2260:
				return "images/Grid_Far_Disc.png";
				break;
			case 2261:
				return "images/Grid_Mall_Disc.png";
				break;
			case 2262:
				return "images/Grid_Mellohi_Disc.png";
				break;
			case 2263:
				return "images/Grid_Stal_Disc.png";
				break;
			case 2264:
				return "images/Grid_Strad_Disc.png";
				break;
			case 2265:
				return "images/Grid_Ward_Disc.png";
				break;
			case 2266:
				return "images/Grid_11_Disc.png";
				break;
			default:
				// TODO: Add a default image for unknown blocks?
				return "air";
				break;		
		}
	}
	function getEnchName($enchId)
	{
		switch($enchId)
		{
			case 0:
				return "Protection";
				break;
			case 1:
				return "Fire Protecion";
				break;
			case 2:
				return "Feather Falling";
				break;
			case 3:
				return "Blast Protection";
				break;
			case 4:
				return "Projectile Protection";
				break;
			case 5:
				return "Respiration";
				break;
			case 6:
				return "Aqua Affinity";
				break;
			case 16:
				return "Sharpness";
				break;
			case 17:
				return "Smite";
				break;
			case 18:
				return "Bane of Arthropods";
				break;
			case 19:
				return "Knockback";
				break;
			case 20:
				return "Fire Aspect";
				break;
			case 21:
				return "Looting";
				break;
			case 32:
				return "Efficiency";
				break;
			case 33:
				return "Silk Touch";
				break;
			case 34:
				return "Unbreaking";
				break;
			case 35:
				return "Fortune";
				break;
			default:
				return "Unknown";
				break;
		}
	}
	function getItemMaxStack($itemId) {
	switch($itemId) {
		case 63:
			return 1;
		case 68:
			return 1;
		case 92:
			return 1;
		case 256:
			return 1;
		case 257:
			return 1;
		case 258:
			return 1;
		case 259:
			return 1;
		case 261:
			return 1;
		case 267:
			return 1;
		case 268:
			return 1;
		case 269:
			return 1;
		case 270:
			return 1;
		case 271:
			return 1;
		case 272:
			return 1;
		case 273:
			return 1;
		case 274:
			return 1;
		case 275:
			return 1;
		case 276:
			return 1;
		case 277:
			return 1;
		case 278:
			return 1;
		case 279:
			return 1;
		case 282:
			return 1;
		case 283:
			return 1;
		case 284:
			return 1;
		case 285:
			return 1;
		case 286:
			return 1;
		case 290:
			return 1;
		case 291:
			return 1;
		case 292:
			return 1;
		case 293:
			return 1;
		case 294:
			return 1;
		case 298:
			return 1;
		case 299:
			return 1;
		case 300:
			return 1;
		case 301:
			return 1;
		case 302:
			return 1;
		case 303:
			return 1;
		case 304:
			return 1;
		case 305:
			return 1;
		case 306:
			return 1;
		case 307:
			return 1;
		case 308:
			return 1;
		case 309:
			return 1;
		case 310:
			return 1;
		case 311:
			return 1;
		case 312:
			return 1;
		case 313:
			return 1;
		case 314:
			return 1;
		case 315:
			return 1;
		case 316:
			return 1;
		case 317:
			return 1;
		case 323:
			return 1;
		case 324:
			return 1;
		case 325:
			return 1;
		case 326:
			return 1;
		case 327:
			return 1;
		case 328:
			return 1;
		case 329:
			return 1;
		case 330:
			return 1;
		case 332:
			return 16;
		case 333:
			return 1;
		case 335:
			return 1;
		case 342:
			return 1;
		case 343:
			return 1;
		case 344:
			return 16;
		case 346:
			return 1;
		case 354:
			return 1;
		case 355:
			return 1;
		case 358:
			return 1;
		case 359:
			return 1;
		case 2256:
			return 1;
		case 2257:
			return 1;
		case 2258:
			return 1;
		case 2259:
			return 1;
		case 2260:
			return 1;
		case 2261:
			return 1;
		case 2262:
			return 1;
		case 2263:
			return 1;
		case 2264:
			return 1;
		case 2265:
			return 1;
		case 2266:
			return 1;
		default:
			return 64;
	}
}
?>
