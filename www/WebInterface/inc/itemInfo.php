<?php


function numberToRoman($num){
  // Make sure that we only use the integer portion of the value
  $n=intval($num);
  $result='';
  // Declare a lookup array that we will use to traverse the number:
  $lookup=array(
    'M' => 1000,
    'CM'=> 900,
    'D' => 500,
    'CD'=> 400,
    'C' => 100,
    'XC'=> 90,
    'L' => 50,
    'XL'=> 40,
    'X' => 10,
    'IX'=> 9,
    'V' => 5,
    'IV'=> 4,
    'I' => 1);
  foreach($lookup as $roman=>$value){
    // Determine the number of matches
    $matches=intval($n / $value);
    // Store that many characters
    $result.=str_repeat($roman, $matches);
    // Substract that from the number
    $n=$n % $value;
  }
  // The Roman numeral should be built, return it
  return $result;
}


function indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }

    return $result;
}





function itemAllowed ($itemId, $itemDamage){
  return true; 
//  switch ($itemId){
//    //Uncomment below to ban stone, dirt and cobblestone being sold.
//    /* 
//    case 1:
//    case 3:
//    case 4:
//      return false;
//    */
//    default: 
//      return true; 
//  }
}

$DamageValues=array(
  256=>251,
  257=>251,
  258=>251,
  259=>65,
  261=>385,
  267=>251,
  268=>60,
  269=>60,
  270=>60,
  271=>60,
  272=>132,
  273=>132,
  274=>132,
  275=>132,
  276=>1562,
  277=>1562,
  278=>1562,
  279=>1562,
  283=>33,
  284=>33,
  285=>33,
  286=>33,
  290=>60,
  291=>132,
  292=>251,
  293=>1562,
  294=>33,
  298=>34,
  299=>49,
  300=>46,
  301=>40,
  302=>67,
  303=>96,
  304=>92,
  305=>79,
  306=>136,
  307=>192,
  308=>184,
  309=>160,
  310=>272,
  311=>384,
  312=>368,
  313=>320,
  314=>68,
  315=>996,
  316=>92,
  317=>80,
  359=>238,
  -1 =>0
);
function isTrueDamage ($itemId, $itemDamage){global $DamageValues;
  if(!isset($DamageValues[$itemId])){
    return(@$DamageValue[-1]);
  }else{
    return($DamageValues[$itemId]);
  }
}


function getMarketPrice($itemTableId, $tableId){
  $table = '';
  switch ($tableId){
    case 0:
      $table = 'WA_Items';
      break;
    case 1:
      $table = 'WA_Auctions';
      break;
    case 2:
      $table = 'WA_Mail';
      break;
    case 3:
      $table = 'WA_SellPrice';
      break;
  }
  $queryItem = mysql_query("SELECT * FROM $table WHERE id='$itemTableId'");
  $itemRow = mysql_fetch_row($queryItem);
  $itemId = $itemRow[1];
  $itemDamage = $itemRow[2];
  $foundIt = false;
  $queryMarket = '';
  //return $itemId;
  $queryEnchantLinks = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemId = '$itemTableId' AND itemTableId = '$tableId'");
  //return mysql_num_rows($queryEnchantLinks);
  $itemEnchantsArray = array();
  while(list($idt, $enchIdt, $itemTableIdt, $itemIdt)= mysql_fetch_row($queryEnchantLinks)){  
    $itemEnchantsArray[] = $enchIdt;
  }
  $queryEnchantLinksMarket = mysql_query("SELECT * FROM WA_EnchantLinks WHERE itemTableId = '4'");
  $base = isTrueDamage($itemId, $itemDamage);
  if ($base > 0){
    if (mysql_num_rows($queryEnchantLinks) == 0){
      $queryMarket1=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemId' AND damage='0' ORDER BY id DESC");
      $maxId = -1;
      $foundIt = false;
      //echo 'first';
      while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1)){
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
      //echo 'second';
      while(list($idm, $namem, $damagem, $timem, $pricem, $refm)= mysql_fetch_row($queryMarket1)){
        $marketEnchantsArray = array ();
        $queryMarket2 = mysql_query("SELECT enchId FROM WA_EnchantLinks WHERE itemId = '$idm' AND itemTableId = '4'");
        while(list($enchIdt)= mysql_fetch_row($queryMarket2)){
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
    //echo 'cant find';
    return 0;
  }else{
    //found get first item
    $rowMarket = mysql_fetch_row($queryMarket);
    $marketId = $rowMarket[0];
    if ($base > 0){$marketPrice = ($rowMarket[4]/$base)*($base - $itemDamage);
    }else{         $marketPrice = $rowMarket[4];}
    return round($marketPrice, 2);
  }
}



function getEnchName($enchId){global $EnchantmentNames;
  if(isset($EnchantmentNames[$enchId])){
    return($EnchantmentNames[$enchId]);
  }else{
    return(@$EnchantmentNames[-1]);
  }
}
$EnchantmentNames=array(
  0=>'Protection',
  1=>'Fire Protecion',
  2=>'Feather Falling',
  3=>'Blast Protection',
  4=>'Projectile Protection',
  5=>'Respiration',
  6=>'Aqua Affinity',
  16=>'Sharpness',
  17=>'Smite',
  18=>'Bane of Arthropods',
  19=>'Knockback',
  20=>'Fire Aspect',
  21=>'Looting',
  32=>'Efficiency',
  33=>'Silk Touch',
  34=>'Unbreaking',
  35=>'Fortune',
  48=>'Power',
  49=>'Punch',
  50=>'Flame',
  51=>'Infinity',
  -1=>'Unknown'
);




function getItemName($itemId, $itemDamage){global $ItemNames,$DamageValues;
  $output='';
  if(isset($ItemNames[$itemId])){
    if(is_array($ItemNames[$itemId])){
      if(isset($ItemNames[$itemId][$itemDamage])){
        $output=$ItemNames[$itemId][$itemDamage];
      }else{
        $output=$ItemNames[$itemId][-1];
      }
    }else{
      $output=$ItemNames[$itemId];
    }
  }else{
    $output=@$ItemNames[-1];
  }
  // damage %
  if(strpos($output,'%damaged%')!==FALSE){
    $output=str_replace('%damaged%', '</b><br /><font size="-1">'.round(($itemDamage/@$DamageValues[$itemId])*100, 1).'% damaged</font><b>' ,$output);
  // map #
  }elseif(strpos($output,'#map#')!==FALSE){
    $output=str_replace('#map#','# '.$itemDamage,$output);
  }
  return('<font size="-1"><b>'.$output.'</b></font>');
}
function getItemImage($itemId, $itemDamage){global $ItemImages;
  if(isset($ItemImages[$itemId])){
    if(is_array($ItemImages[$itemId])){
      if(isset($ItemImages[$itemId][$itemDamage])){
        return('images/'.$ItemImages[$itemId][$itemDamage]);
      }else{
        return('images/'.$ItemImages[$itemId][-1]);
      }
    }else{
      return('images/'.$ItemImages[$itemId]);
    }
  }else{
    return('images/'.@$ItemImages[-1]);
  }
}

$ItemNames=array(
  1=>'Stone',
  2=>'Grass',
  3=>'Dirt',
  4=>'Cobblestone',
  5=>array(
    0 =>'Wooden Plank (Oak)',
    1 =>'Wooden Plank (Pine)',
    2 =>'Wooden Plank (Birch)',
    3 =>'Wooden Plank (Jungle)',
    -1=>'Wooden Plank'
  ),
  6=>array(
    0 =>'Oak Sapling',
    1 =>'Pine Sapling',
    2 =>'Birch Sapling',
    3 =>'Jungle Sapling',
    -1=>'Sapling'
  ),
  7=>'Bedrock',
  8=>'Water',
  9=>'Stationary Water',
  10=>'Lava',
  11=>'Stationary Lava',
  12=>'Sand',
  13=>'Gravel',
  14=>'Gold Ore',
  15=>'Iron Ore',
  16=>'Coal Ore',
  17=>array(
    0 =>'Oak Log',
    1 =>'Pine Log',
    2 =>'Birch Log',
    3 =>'Jungle Log',
    -1=>'Log'
  ),
  18=>array(
    0 =>'Oak Leaves',
    4 =>'Oak Leaves',
    1 =>'Pine Leaves',
    5 =>'Pine Leaves',
    2 =>'Birch Leaves',
    6 =>'Birch Leaves',
    3 =>'Jungle Leaves',
    7 =>'Jungle Leaves',
    -1=>'Leaves'
  ),
  19=>'Sponge',
  20=>'Glass',
  21=>'Lapis Lazuli Ore',
  22=>'Lapis Lazuli Block',
  23=>'Dispenser',
  24=>array(
    0 =>'Sandstone',
    1 =>'Decorative Sandstone',
    2 =>'Smooth Sandstone',
    -1=>'Sandstone'
  ),
  25=>'Note Block',
  26=>'Bed Block',
  27=>'Powered Rail',
  28=>'Detector Rail',
  29=>'Sticky Piston',
  30=>'Web',
  31=>array(
    0 =>'Dead Shrub',
    1 =>'Tall Grass',
    2 =>'Live Shrub',
    -1=>'Unknown Shrub'
  ),
  32=>'Dead Shrub',
  33=>'Piston',
  34=>'Piston Head',
  35=>array(
    0 =>'White Wool',
    1 =>'Orange Wool',
    2 =>'Magenta Wool',
    3 =>'Light Blue Wool',
    4 =>'Yellow Wool',
    5 =>'Light Green Wool',
    6 =>'Pink Wool',
    7 =>'Grey Wool',
    8 =>'Light Grey Wool',
    9 =>'Cyan Wool',
    10=>'Purple Wool',
    11=>'Blue Wool',
    12=>'Brown Wool',
    13=>'Dark Green Wool',
    14=>'Red Wool',
    15=>'Black Wool',
    -1=>'Unknown Wool'
  ),
  37=>'Dandelion',
  38=>'Rose',
  39=>'Brown Mushroom',
  40=>'Red Mushroom',
  41=>'Gold Block',
  42=>'Iron Block',
  43=>array(
    0 =>'Double Stone Slab',
    1 =>'Double Sandstone Slab',
    2 =>'Double Wooden Slab',
    3 =>'Double Cobblestone Slab',
    -1=>'Unknown Double Slab'
  ),
  44=>array(
    0=>'Stone Slab',
    1=>'Sandstone Slab',
    2=>'Wooden Slab',
    3=>'Cobblestone Slab',
    -1=>'Unknown Slab'
  ),
  45=>'Brick Block',
  46=>'TNT',
  47=>'Bookshelf',
  48=>'Mossy Cobblestone',
  49=>'Obsidian',
  50=>'Torch',
  51=>'Fire',
  52=>'Monster Spawner',
  53=>'Wooden Stairs',
  54=>'Chest',
  55=>'Redstone Wire',
  56=>'Diamond Ore',
  57=>'Diamond Block',
  58=>'Workbench',
  59=>'Crops',
  60=>'Soil',
  61=>'Furnace',
  62=>'Burning Furnace',
  63=>'Sign Post',
  64=>'Wooden Door Block',
  65=>'Ladder',
  66=>'Rail',
  67=>'Cobblestone Stairs',
  68=>'Wall Sign',
  69=>'Lever',
  70=>'Stone Pressure Plate',
  71=>'Iron Door Block',
  72=>'Wooden Pressure Plate',
  73=>'Redstone Ore',
  74=>'Glowing Redstone Ore',
  75=>'Redstone Torch',
  76=>'Redstone Torch',
  77=>'Stone Button',
  78=>'Snow',
  79=>'Ice',
  80=>'Snow Block',
  81=>'Cactus',
  82=>'Clay',
  83=>'Sugar Cane',
  84=>'Jukebox',
  85=>'Fence',
  86=>'Pumpkin',
  87=>'Netherrack',
  88=>'Soul Sand',
  89=>'Glowstone',
  90=>'Portal',
  91=>'Jack-O-Lantern',
  92=>'Cake',
  93=>'Redstone Repeater',
  94=>'Redstone Repeater',
  95=>'Locked Chest',
  96=>'Trapdoor',
  97=>'Silverfish Stone',
  98=>array(
    0 =>'Stone Brick',
    1 =>'Mossy Stone Brick',
    2 =>'Cracked Stone Brick',
    3 =>'Circle Stone Brick',
    -1=>'Unknown Brick'
  ),
  99=>'Brown Mushroom Cap',
  100=>'Red Mushroom Cap',
  101=>'Iron Bars',
  102=>'Glass Pane',
  103=>'Melon',
  104=>'Pumpkin Stem',
  105=>'Melon Stem',
  106=>'Vines',
  107=>'Fence Gate',
  108=>'Brick Stairs',
  109=>'Stone Brick Stairs',
  110=>'Mycelium',
  111=>'Lily Pad',
  112=>'Nether Brick',
  113=>'Nether Brick Fence',
  114=>'Nether Brick Stairs',
  115=>'Nether Wart',
  116=>'Enchantment Table',
  117=>'Brewing Stand',
  118=>'Cauldron',
  119=>'End Portal',
  120=>'End Portal Frame',
  121=>'End Stone',
  122=>'Dragon Egg',
  123=>'Redstone Lamp',
  256=>'Iron Shovel %damaged%',
  257=>'Iron Pickaxe %damaged%',
  258=>'Iron Axe %damaged%',
  259=>'Flint and Steel %damaged%',
  260=>'Apple',
  261=>'Bow %damaged%',
  262=>'Arrow',
  263=>array(
    0 =>'Coal',
    1 =>'Charcoal',
    -1=>'Unknown Coal'
  ),
  264=>'Diamond',
  265=>'Iron Ingot',
  266=>'Gold Ingot',
  267=>'Iron Sword %damaged%',
  268=>'Wooden Sword %damaged%',
  269=>'Wooden Shovel %damaged%',
  270=>'Wooden Pickaxe %damaged%',
  271=>'Wooden Axe %damaged%',
  272=>'Stone Sword %damaged%',
  273=>'Stone Shovel %damaged%',
  274=>'Stone Pickaxe %damaged%',
  275=>'Stone Axe %damaged%',
  276=>'Diamond Sword %damaged%',
  277=>'Diamond Shovel %damaged%',
  278=>'Diamond Pickaxe %damaged%',
  279=>'Diamond Axe %damaged%',
  280=>'Stick',
  281=>'Bowl',
  282=>'Mushroom Soup',
  283=>'Gold Sword %damaged%',
  284=>'Gold Shovel %damaged%',
  285=>'Gold Pickaxe %damaged%',
  286=>'Gold Axe %damaged%',
  287=>'String',
  288=>'Feather',
  289=>'Gunpowder',
  290=>'Wooden Hoe %damaged%',
  291=>'Stone Hoe %damaged%',
  292=>'Iron Hoe %damaged%',
  293=>'Diamond Hoe %damaged%',
  294=>'Gold Hoe %damaged%',
  295=>'Wheat Seeds',
  296=>'Wheat',
  297=>'Bread',
  298=>'Leather Helmet %damaged%',
  299=>'Leather Chestplate %damaged%',
  300=>'Leather Leggings %damaged%',
  301=>'Leather Boots %damaged%',
  302=>'Chain Mail Helmet %damaged%',
  303=>'Chain Mail Chestplate %damaged%',
  304=>'Chain Mail Leggings %damaged%',
  305=>'Chain Mail Boots %damaged%',
  306=>'Iron Helmet %damaged%',
  307=>'Iron Chestplate %damaged%',
  308=>'Iron Leggings %damaged%',
  309=>'Iron Boots %damaged%',
  310=>'Diamond Helmet %damaged%',
  311=>'Diamond Chestplate %damaged%',
  312=>'Diamond Leggings %damaged%',
  313=>'Diamond Boots %damaged%',
  314=>'Gold Helmet %damaged%',
  315=>'Gold Chestplate %damaged%',
  316=>'Gold Leggings %damaged%',
  317=>'Gold Boots %damaged%',
  318=>'Flint',
  319=>'Raw Porkchop',
  320=>'Cooked Porkchop',
  321=>'Painting',
  322=>'Golden Apple',
  323=>'Sign',
  324=>'Wooden Door',
  325=>'Bucket',
  326=>'Bucket of Water',
  327=>'Bucket of Lava',
  328=>'Minecart',
  329=>'Saddle',
  330=>'Iron Door',
  331=>'Redstone',
  332=>'Snowball',
  333=>'Boat',
  334=>'Leather',
  335=>'Bucket of Milk',
  336=>'Clay Brick',
  337=>'Clay Balls',
  338=>'Sugar Cane',
  339=>'Paper',
  340=>'Book',
  341=>'Slimeball',
  342=>'Storage Minecart',
  343=>'Powered Minecart',
  344=>'Egg',
  345=>'Compass',
  346=>'Fishing Rod',
  347=>'Clock',
  348=>'Glowstone Dust',
  349=>'Raw Fish',
  350=>'Cooked Fish',
  351=>array(
    0 =>'Ink Sack',
    1 =>'Rose Red',
    2 =>'Cactus Green',
    3 =>'Cocoa Beans',
    4 =>'Lapis Lazuli',
    5 =>'Purple Dye',
    6 =>'Cyan Dye',
    7 =>'Light Grey Dye',
    8 =>'Grey Dye',
    9 =>'Pink Dye',
    10=>'Lime Dye',
    11=>'Dandelion Yellow',
    12=>'Light Blue Dye',
    13=>'Magenta Dye',
    14=>'Orange Dye',
    15=>'Bone Meal',
    -1=>'Unknown Dye'
  ),
  352=>'Bone',
  353=>'Sugar',
  354=>'Cake',
  355=>'Bed',
  356=>'Redstone Repeater',
  357=>'Cookie',
  358=>'Map #map#',
  359=>'Shears %damaged%',
  360=>'Melon Slice',
  361=>'Pumpkin Seeds',
  362=>'Melon Seeds',
  363=>'Raw Beef',
  364=>'Steak',
  365=>'Raw Chicken',
  366=>'Cooked Chicken',
  367=>'Rotten Flesh',
  368=>'Ender Pearl',
  369=>'Blaze Rod',
  370=>'Ghast Tear',
  371=>'Gold Nugget',
  372=>'Nether Wart',
  373=>array(
    0    =>'Bottle of Water',
    16   =>'Awkward Potion',
    32   =>'Thick Potion',
    64   =>'Mundane Potion',
    8193 =>'Regeneration Potion (0=>45)',
    8194 =>'Swiftness Potion (3=>00)',
    8195 =>'Fire Resistance Potion (3=>00)',
    8196 =>'Poison Potion (0=>45)',
    8197 =>'Healing Potion',
    8200 =>'Weakness Potion (1=>30)',
    8201 =>'Strength Potion (3=>00)',
    8202 =>'Slowness Potion (1=>30)',
    8204 =>'Harming Potion',
    8225 =>'Regeneration Potion II (0=>22)',
    8226 =>'Swiftness Potion II (1=>30)',
    8228 =>'Poison Potion II (0=>22)',
    8229 =>'Healing Potion II',
    8233 =>'Strength Potion II (1=>30)',
    8236 =>'Harming Potion II',
    8257 =>'Regeneration Potion (2=>00)',
    8258 =>'Swiftness Potion (8=>00)',
    8259 =>'Fire Resistance Potion (8=>00)',
    8260 =>'Poison Potion (2=>00)',
    8264 =>'Weakness Potion (4=>00)',
    8265 =>'Strength Potion (8=>00)',
    8266 =>'Slowness Potion (4=>00)',
    16378=>'Fire Resistance Splash (2=>15)',
    16385=>'Regeneration Splash (0=>33)',
    16386=>'Swiftness Splash (2=>15)',
    16388=>'Poison Splash (0=>33)',
    16389=>'Healing Splash',
    16392=>'Weakness Splash (1=>07)',
    16393=>'Strength Splash (2=>15)',
    16394=>'Slowness Splash (2=>15)',
    16396=>'Harming Splash',
    16418=>'Swiftness Splash II (1=>07)',
    16420=>'Poison Splash II (0=>16)',
    16421=>'Healing Splash II',
    16425=>'Strength Splash II (1=>07)',
    16428=>'Harming Splash II',
    16449=>'Regeneration Splash (1=>30)',
    16450=>'Swiftness Splash (6=>00)',
    16451=>'Fire Resistance Splash (6=>00)',
    16452=>'Poison Splash (1=>30)',
    16456=>'Weakness Splash (3=>00)',
    16457=>'Strength Splash (6=>00)',
    16458=>'Slowness Splash (3=>00)',
    16471=>'Regeneration Splash II (0=>16)',
    -1   =>'Clear Potion'
  ),
  374=>'Glass Bottle',
  375=>'Spider Eye',
  376=>'Fermented Spider Eye',
  377=>'Blaze Powder',
  378=>'Magma Cream',
  379=>'Brewing Stand',
  380=>'Cauldron',
  381=>'Eye of Ender',
  382=>'Glistering Melon (Slice)',
  383=>array(
    50 =>'Spawn Creeper',
    51 =>'Spawn Skeleton',
    52 =>'Spawn Spider',
    54 =>'Spawn Zombie',
    55 =>'Spawn Slime',
    56 =>'Spawn Ghast',
    57 =>'Spawn Pig Zombie',
    58 =>'Spawn Enderman',
    59 =>'Spawn Cave_Spider',
    60 =>'Spawn Silverfish',
    61 =>'Spawn Blaze',
    62 =>'Spawn Magma Cube',
    90 =>'Spawn Pig',
    91 =>'Spawn Sheep',
    92 =>'Spawn Cow',
    93 =>'Spawn Chicken',
    94 =>'Spawn Squid',
    95 =>'Spawn Wolf',
    96 =>'Spawn Mooshroom',
    98 =>'Spawn Ocelot',
    120=>'Spawn Villager',
    -1 =>'Spawn Unknown'
  ),
  384=>'Bottle o\' Enchanting',
  385=>'Fire Charge',
  2256=>'Music Disc (13)',
  2257=>'Music Disc (Cat)',
  2258=>'Music Disc (Blocks)',
  2259=>'Music Disc (Chirp)',
  2260=>'Music Disc (Far)',
  2261=>'Music Disc (Mall)',
  2262=>'Music Disc (Mellohi)',
  2263=>'Music Disc (Stal)',
  2264=>'Music Disc (Strad)',
  2265=>'Music Disc (Ward)',
  2266=>'Music Disc (11)',
  // TODO=> Return 'Unknown (id=>value)' instead?
  -1  =>'Unknown Item Id=> .$itemId.($itemDamage==0?""=>=>.$itemDamage)'
);


$ItemImages=array(
  1=>'Stone.png',
  2=>'Grass.png',
  3=>'Dirt.png',
  4=>'Cobblestone.png',
  5=>array(
    0 =>'Wooden_Plank_Oak.png',
    1 =>'Wooden_Plank_Pine.png',
    2 =>'Wooden_Plank_Birch.png',
    3 =>'Wooden_Plank_Jungle.png',
    -1=>'Wooden_Plank.png'
  ),
  6=>array(
    0 =>'Sapling_Oak.png',
    1 =>'Sapling_Pine.png',
    2 =>'Sapling_Birch.png',
    3 =>'Sapling_Jungle.png',
    -1=>'Sapling.png'
  ),
  7=>'Bedrock.png',
  8=>'Water.png',
  9=>'Water.png',
  10=>'Lava.png',
  11=>'Lava.png',
  12=>'Sand.png',
  13=>'Gravel.png',
  14=>'Gold_Ore.png',
  15=>'Iron_Ore.png',
  16=>'Coal_Ore.png',
  17=>array(
    0 =>'Log_Oak.png',
    1 =>'Log_Pine.png',
    2 =>'Log_Birch.png',
    3 =>'Log_Jungle.png',
    -1=>'Log.png'
  ),
  18=>array(
    0 =>'Leaves_Oak.png',
    4 =>'Leaves_Oak.png',
    1 =>'Leaves_Pine.png',
    5 =>'Leaves_Pine.png',
    2 =>'Leaves_Birch.png',
    6 =>'Leaves_Birch.png',
    3 =>'Leaves_Jungle.png',
    7 =>'Leaves_Jungle.png',
    -1=>'Leaves.png'
  ),
  19=>'Sponge.png',
  20=>'Glass.png',
  21=>'Lapis_Lazuli_Ore.png',
  22=>'Lapis_Lazuli_Block.png',
  23=>'Dispenser.png',
  24=>array(
    0 =>'Sandstone.png',
    1 =>'Sandstone_Decorative.png',
    2 =>'Sandstone_Smooth.png',
    -1=>'Sandstone.png'
  ),
  25=>'Note_Block.png',
  26=>'Bed.png',
  27=>'Rail_Powered.png',
  28=>'Rail_Detector.png',
  29=>'Sticky_Piston.png',
  30=>'Cobweb.png',
  31=>array(
    0 =>'Tall_Grass1.png',
    1 =>'Tall_Grass2.png',
    2 =>'Tall_Grass3.png',
    -1=>'Tall_Grass1.png'
  ),
  32=>'Dead_Bush.png',
  33=>'Piston.png',
  34=>array(
    0 =>'Piston.png',
    1 =>'Sticky_Piston.png',
    -1=>'Piston.png'
  ),
  35=>array(
    0 =>'Wool_White.png',
    1 =>'Wool_Orange.png',
    2 =>'Wool_Magenta.png',
    3 =>'Wool_Light_Blue.png',
    4 =>'Wool_Yellow.png',
    5 =>'Wool_Lime.png',
    6 =>'Wool_Pink.png',
    7 =>'Wool_Gray.png',
    8 =>'Wool_Light_Gray.png',
    9 =>'Wool_Cyan.png',
    10=>'Wool_Purple.png',
    11=>'Wool_Blue.png',
    12=>'Wool_Brown.png',
    13=>'Wool_Green.png',
    14=>'Wool_Red.png',
    15=>'Wool_Black.png',
    -1=>'Wool_White.png'
  ),
  37=>'Dandelion.png',
  38=>'Rose.png',
  39=>'Mushroom_Brown.png',
  40=>'Mushroom_Red.png',
  41=>'Gold_Block.png',
  42=>'Iron_Block.png',
  // subitems 1, 2, and 3 not supported by minecraft
  43=>array(
    0 =>'Slab_Double_Stone.png',
    1 =>'Sandstone.png',
    2 =>'Wood.png',
    3 =>'Cobblestone.png',
    -1=>'Slab_Double_Stone.png'
  ),
  44=>array(
    0 =>'Slab_Stone.png',
    1 =>'Slab_Sandstone.png',
    2 =>'Slab_Wood.png',
    3 =>'Slab_Cobblestone.png',
    -1=>'Slab_Stone.png'
  ),
  45=>'Brick_Block.png',
  46=>'TNT.png',
  47=>'Bookshelf.png',
  48=>'Cobblestone_Moss.png',
  49=>'Obsidian.png',
  50=>'Torch.png',
  51=>'Fire.png',
  52=>'Monster_Spawner.png',
  53=>'Stairs_Wooden.png',
  54=>'Chest.png',
  55=>'Redstone_Dust.png',
  56=>'Diamond_Ore.png',
  57=>'Diamond_Block.png',
  58=>'Crafting_Table.png',
  // TODO=> add crop picture(s)
  59=>'Crops',
  60=>'Farmland.png',
  61=>'Furnace.png',
  62=>'Furnace.png',
  63=>'Sign.png',
  64=>'Door_Wood.png',
  65=>'Ladder.png',
  66=>'Rails.png',
  67=>'Stairs_Cobblestone.png',
  68=>'Sign.png',
  69=>'Lever.png',
  70=>'Pressure_Plate_Stone.png',
  71=>'Door_Iron.png',
  72=>'Pressure_Plate_Wood.png',
  73=>'Redstone_Ore.png',
  74=>'Redstone_Ore.png',
  75=>'Redstone_Torch.png',
  76=>'Redstone_Torch.png',
  77=>'Stone_Button.png',
  78=>'Snow_Block.png',
  79=>'Ice.png',
  80=>'Snow_Block.png',
  81=>'Cactus.png',
  82=>'Clay_Block.png',
  83=>'Sugar_Cane.png',
  84=>'Jukebox.png',
  85=>'Fence.png',
  86=>'Pumpkin.png',
  87=>'Netherrack.png',
  88=>'Soul_Sand.png',
  89=>'Glowstone_Block.png',
  90=>'Portal.png',
  91=>'Jack-O-Lantern.png',
  92=>'Cake.png',
  93=>'Redstone_Repeater.png',
  94=>'Redstone_Repeater.png',
  95=>'Chest.png',
  96=>'Trapdoor.png',
  98=>array(
    0 =>'Stone_Brick.png',
    1 =>'Stone_Brick_Mossy.png',
    2 =>'Stone_Brick_Cracked.png',
    3 =>'Stone_Brick_Circle.png',
    -1=>'Stone_Brick.png'
  ),
  99=>'Mushroom_Brown.png',
  100=>'Mushroom_Red.png',
  101=>'Iron_Bars.png',
  102=>'Glass_Pane.png',
  103=>'Melon_Block.png',
  // TODO=> add pumpkin and melon steam pictures
  //104=>'Pumpkin Stem',
  //105=>'Melon Stem',
  106=>'Vines.png',
  107=>'Fence_Gate.png',
  108=>'Stairs_Brick.png',
  109=>'Stairs_Stone_Brick.png',
  110=>'Mycelium.png',
  111=>'Lily_Pad.png',
  112=>'Nether_Brick.png',
  113=>'Nether_Brick_Fence.png',
  114=>'Stairs_Nether_Brick.png',
  115=>'Nether_Wart.png',
  116=>'Enchantment_Table.png',
  117=>'Brewing_Stand.png',
  118=>'Cauldron.png',
  119=>'End_Portal.png',
  120=>'End_Portal_Frame.png',
  121=>'End_Stone.png',
  122=>'Dragon_Egg.png',
  123=>'Redstone_Lamp.png',
  256=>'Iron_Shovel.png',
  257=>'Iron_Pickaxe.png',
  258=>'Iron_Axe.png',
  259=>'Flint_and_Steel.png',
  260=>'Apple_Red.png',
  261=>'Bow.png',
  262=>'Arrow.png',
  263=>array(
    0 =>'Coal_Item.png',
    1 =>'Charcoal_Item.png',
    -1=>'Coal_Item.png'
  ),
  264=>'Diamond_Gem.png',
  265=>'Iron_Ingot.png',
  266=>'Gold_Ingot.png',
  267=>'Iron_Sword.png',
  268=>'Wooden_Sword.png',
  269=>'Wooden_Shovel.png',
  270=>'Wooden_Pickaxe.png',
  271=>'Wooden_Axe.png',
  272=>'Stone_Sword.png',
  273=>'Stone_Shovel.png',
  274=>'Stone_Pickaxe.png',
  275=>'Stone_Axe.png',
  276=>'Diamond_Sword.png',
  277=>'Diamond_Shovel.png',
  278=>'Diamond_Pickaxe.png',
  279=>'Diamond_Axe.png',
  280=>'Stick.png',
  281=>'Bowl.png',
  282=>'Mushroom_Stew.png',
  283=>'Gold_Sword.png',
  284=>'Gold_Shovel.png',
  285=>'Gold_Pickaxe.png',
  286=>'Gold_Axe.png',
  287=>'String.png',
  288=>'Feather.png',
  289=>'Gunpowder.png',
  290=>'Wooden_Hoe.png',
  291=>'Stone_Hoe.png',
  292=>'Iron_Hoe.png',
  293=>'Diamond_Hoe.png',
  294=>'Gold_Hoe.png',
  295=>'Seeds_Wheat.png',
  296=>'Wheat.png',
  297=>'Bread.png',
  298=>'Leather_Helmet.png',
  299=>'Leather_Chestplate.png',
  300=>'Leather_Pants.png',
  301=>'Leather_Boots.png',
  302=>'Chain_Armor_Helmet.png',
  303=>'Chain_Armor_Chestplate.png',
  304=>'Chain_Armor_Leggings.png',
  305=>'Chain_Armor_Boots.png',
  306=>'Iron_Helmet.png',
  307=>'Iron_Chestplate.png',
  308=>'Iron_Leggings.png',
  309=>'Iron_Boots.png',
  310=>'Diamond_Helmet.png',
  311=>'Diamond_Chestplate.png',
  312=>'Diamond_Leggings.png',
  313=>'Diamond_Boots.png',
  314=>'Gold_Helmet.png',
  315=>'Gold_Chestplate.png',
  316=>'Gold_Leggings.png',
  317=>'Gold_Boots.png',
  318=>'Flint.png',
  319=>'Porkchop_Raw.png',
  320=>'Porkchop_Cooked.png',
  321=>'Painting.png',
  322=>'Apple_Golden.png',
  323=>'Sign.png',
  324=>'Door_Wood.png',
  325=>'Bucket.png',
  326=>'Bucket_Water.png',
  327=>'Bucket_Lava.png',
  328=>'Minecart.png',
  329=>'Saddle.png',
  330=>'Door_Iron.png',
  331=>'Redstone_Dust.png',
  332=>'Snowball.png',
  333=>'Boat.png',
  334=>'Leather.png',
  335=>'Bucket_Milk.png',
  336=>'Clay_Brick.png',
  337=>'Clay_Item.png',
  338=>'Sugar_Cane.png',
  339=>'Paper.png',
  340=>'Book.png',
  341=>'Slimeball.png',
  342=>'Minecart_Storage.png',
  343=>'Minecart_Powered.png',
  344=>'Egg.png',
  345=>'Compass.png',
  346=>'Fishing_Rod.png',
  347=>'Clock.png',
  348=>'Glowstone_Dust.png',
  349=>'Fish_Raw.png',
  350=>'Fish_Cooked.png',
  351=>array(
    0 =>'Ink_Sac.png',
    1 =>'Rose_Red.png',
    2 =>'Cactus_Green.png',
    3 =>'Cocoa_Beans.png',
    4 =>'Lapis_Lazuli_Dye.png',
    5 =>'Dye_Purple.png',
    6 =>'Dye_Cyan.png',
    7 =>'Dye_Light_Gray.png',
    8 =>'Dye_Gray.png',
    9 =>'Dye_Pink.png',
    10=>'Dye_Lime.png',
    11=>'Dandelion_Yellow.png',
    12=>'Dye_Light_Blue.png',
    13=>'Dye_Magenta.png',
    14=>'Dye_Orange.png',
    15=>'Bone_Meal.png',
    -1=>'Bone_Meal.png'
  ),
  352=>'Bone.png',
  353=>'Sugar.png',
  354=>'Cake.png',
  355=>'Bed.png',
  356=>'Redstone_Repeater.png',
  357=>'Cookie.png',
  358=>'Map_Item.png',
  359=>'Shears.png',
  360=>'Melon_Slice.png',
  361=>'Seeds_Pumpkin.png',
  362=>'Seeds_Melon.png',
  363=>'Beef_Raw.png',
  364=>'Beef_Cooked.png',
  365=>'Chicken_Raw.png',
  366=>'Chicken_Cooked.png',
  367=>'Rotten_Flesh.png',
  368=>'Ender_Pearl.png',
  369=>'Blaze_Rod.png',
  370=>'Ghast_Tear.png',
  371=>'Gold_Nugget.png',
  372=>'Seeds_Nether_Wart.png',
  373=>array(
    0    =>'Bottle_Water.png',
    16   =>'Potion_Awkward.png',
    32   =>'Potion_Thick.png',
    64   =>'Potion_Mundane.png',
    8193 =>'Potion_of_Regeneration.png',
    8194 =>'Potion_of_Swiftness.png',
    8195 =>'Potion_of_Fire_Resistance.png',
    8196 =>'Potion_of_Poison.png',
    8197 =>'Instant_Health.png',
    8200 =>'Potion_of_Poison.png',
    8201 =>'Potion_of_Strength.png',
    8202 =>'Potion_of_Slowness.png',
    8204 =>'Potion_of_Harming.png',
    8225 =>'Potion_of_Regeneration.png',
    8226 =>'Potion_of_Swiftness.png',
    8228 =>'Potion_of_Poison.png',
    8229 =>'Instant_Health.png',
    8233 =>'Potion_of_Strength.png',
    8236 =>'Potion_of_Harming.png',
    8257 =>'Potion_of_Regeneration.png',
    8258 =>'Potion_of_Swiftness.png',
    8259 =>'Potion_of_Fire_Resistance.png',
    8260 =>'Potion_of_Poison.png',
    8264 =>'Potion_of_Weakness.png',
    8265 =>'Potion_of_Strength.png',
    8266 =>'Potion_of_Slowness.png',
    16378=>'Potion_Fire_Resistance_Splash.png',
    16385=>'Potion_Regeneration_Splash.png',
    16386=>'Potion_Swiftness_Splash.png',
    16388=>'Potion_Poison_Splash.png',
    16389=>'Potion_Healing_Splash.png',
    16392=>'Potion_Weakness_Splash.png',
    16393=>'Potion_Strength_Splash.png',
    16394=>'Potion_Slowness_Splash.png',
    16396=>'Potion_Harming_Splash.png',
    16418=>'Potion_Swiftness_Splash.png',
    16420=>'Potion_Poison_Splash.png',
    16421=>'Potion_Healing_Splash.png',
    16425=>'Potion_Strength_Splash.png',
    16428=>'Potion_Harming_Splash.png',
    16449=>'Potion_Regneration_Splash.png',
    16450=>'Potion_Swiftness_Splash.png',
    16451=>'Potion_Fire_Resistance_Splash.png',
    16452=>'Potion_Poison_Splash.png',
    16456=>'Potion_Weakness_Splash.png',
    16457=>'Potion_Strength_Splash.png',
    16458=>'Potion_Slowness_Splash.png',
    16471=>'Potion_Regeneration_Splash.png',
    -1   =>'Bottle_Water.png'
  ),
  374=>'Bottle_Glass.png',
  375=>'Spider_Eye.png',
  376=>'Fermented_Spider_Eye.png',
  377=>'Blaze_Powder.png',
  378=>'Magma_Cream.png',
  379=>'Brewing_Stand.png',
  380=>'Cauldron.png',
  381=>'Eye_of_Ender.png',
  382=>'Melon_Glistering.png',
  383=>array(
    50 =>'Spawn_Creeper.png',
    51 =>'Spawn_Skeleton.png',
    52 =>'Spawn_Spider.png',
    54 =>'Spawn_Zombie.png',
    55 =>'Spawn_Slime.png',
    56 =>'Spawn_Ghast.png',
    57 =>'Spawn_Pig_Zombie.png',
    58 =>'Spawn_Enderman.png',
    59 =>'Spawn_Cave_Spider.png',
    60 =>'Spawn_Silverfish.png',
    61 =>'Spawn_Blaze.png',
    62 =>'Spawn_Magma_Cube.png',
    90 =>'Spawn_Pig.png',
    91 =>'Spawn_Sheep.png',
    92 =>'Spawn_Cow.png',
    93 =>'Spawn_Chicken.png',
    94 =>'Spawn_Squid.png',
    95 =>'Spawn_Wolf.png',
    96 =>'Spawn_Mooshroom.png',
    98 =>'Spawn_Ocelot.png',
    120=>'Spawn_Villager.png',
    -1 =>'Egg.png'
  ),
  384=>'Bottle_o_Enchanting.png',
  385=>'Fire_Charge.png',
  2256=>'Disc_Gold.png',
  2257=>'Disc_Green.png',
  2258=>'Disc_Blocks.png',
  2259=>'Disc_Chirp.png',
  2260=>'Disc_Far.png',
  2261=>'Disc_Mall.png',
  2262=>'Disc_Mellohi.png',
  2263=>'Disc_Stal.png',
  2264=>'Disc_Strad.png',
  2265=>'Disc_Ward.png',
  2266=>'Disc_11.png',
  // TODO=> Add a default image for unknown blocks?
  -1=>'Unknown.png'
);











function getItemMaxStack($itemId){global $ItemMaxStacks;
  if(!isset($ItemMaxStacks[$itemId])){
    return(@$ItemMaxStacks[-1]);
  }else{
    return($ItemMaxStacks[$itemId]);
  }
}
$ItemMaxStacks=array(
  // Sign Post
  63=>1,
  // Wooden Door Block
  64=>1,
  // Wall Sign
  68=>1,
  // Iron Door Block
  71=>1,
  // Cake
  92=>1,
  // Iron Shovel
  256=>1,
  // Iron Pickaxe
  257=>1,
  // Iron Axe
  258=>1,
  // Flint and Steel
  259=>1,
  // Bow
  261=>1,
  // Iron Sword
  267=>1,
  // Wooden Sword
  268=>1,
  // Wooden Shovel
  269=>1,
  // Wooden Pickaxe
  270=>1,
  // Wooden Axe
  271=>1,
  // Stone Sword
  272=>1,
  // Stone Shovel
  273=>1,
  // Stone Pickaxe
  274=>1,
  // Stone Axe
  275=>1,
  // Diamond Sword
  276=>1,
  // Diamond Shovel
  277=>1,
  // Diamond Pickaxe
  278=>1,
  // Diamond Axe
  279=>1,
  // Mushroom Soup
  282=>1,
  // Gold Sword
  283=>1,
  // Gold Shovel
  284=>1,
  // Gold Pickaxe
  285=>1,
  // Gold Axe
  286=>1,
  // Wooden Hoe
  290=>1,
  // Stone Hoe
  291=>1,
  // Iron Hoe
  292=>1,
  // Diamond Hoe
  293=>1,
  // Gold Hoe
  294=>1,
  // Leather Helmet
  298=>1,
  // Leather Chestplate
  299=>1,
  // Leather Leggings
  300=>1,
  // Leather Boots
  301=>1,
  // Chain Mail Helmet
  302=>1,
  // Chain Mail Chestplate
  303=>1,
  // Chain Mail Leggings
  304=>1,
  // Chain Mail Boots
  305=>1,
  // Iron Helmet
  306=>1,
  // Iron Chestplate
  307=>1,
  // Iron Leggings
  308=>1,
  // Iron Boots
  309=>1,
  // Diamond Helmet
  310=>1,
  // Diamond Chestplate
  311=>1,
  // Diamond Leggings
  312=>1,
  // Diamond Boots
  313=>1,
  // Gold Helmet
  314=>1,
  // Gold Chestplate
  315=>1,
  // Gold Leggings
  316=>1,
  // Gold Boots
  317=>1,
  // Sign
  323=>1,
  // Wooden Door
  324=>1,
  // Bucket
  325=>1,
  // Water Bucket
  326=>1,
  // Lava Bucket
  327=>1,
  // Minecart
  328=>1,
  // Saddle
  329=>1,
  // Iron Door
  330=>1,
  // Snowball
  332=>16,
  // Boat
  333=>1,
  // Milk Bucket
  335=>1,
  // Storage Minecart
  342=>1,
  // Powered Minecart
  343=>1,
  // Egg
  344=>16,
  // Fishing Rod
  346=>1,
  // Cake
  354=>1,
  // Bed
  355=>1,
  // Map
  358=>1,
  // Shears
  359=>1,
  // Potion
  373=>1,
  // Music Disc (13)
  2256=>1,
  // Music Disc (Cat)
  2257=>1,
  // Music Disc (Blocks)
  2258=>1,
  // Music Disc (Chirp)
  2259=>1,
  // Music Disc (Far)
  2260=>1,
  // Music Disc (Mall)
  2261=>1,
  // Music Disc (Mellohi)
  2262=>1,
  // Music Disc (Stal)
  2263=>1,
  // Music Disc (Strad)
  2264=>1,
  // Music Disc (Ward)
  2265=>1,
  // Music Disc (11)
  2266=>1,
  // Default
  -1=>64
);


?>
