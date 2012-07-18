package me.lorenzop.webauctionplus.mysql;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.SortedSet;
import java.util.TreeSet;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.Auction;
import me.lorenzop.webauctionplus.dao.AuctionPlayer;

import org.bukkit.Bukkit;
import org.bukkit.Location;
import org.bukkit.World;
import org.bukkit.enchantments.Enchantment;
import org.bukkit.inventory.ItemStack;

public class DataQueries extends MySQLConnPool {


	public DataQueries(String dbHost, int dbPort, String dbUser,
			String dbPass, String dbName, String dbPrefix, boolean debugSQL) {
		DataQueries.logPrefix = WebAuctionPlus.logPrefix;
		this.dbHost = dbHost;
		this.dbPort = dbPort;
		this.dbUser = dbUser;
		this.dbPass = dbPass;
		this.dbName = dbName;
		this.dbPrefix = dbPrefix;
		this.debugSQL = debugSQL;
	}
	public void start() {
		Connection conn = getConnection();
		closeResources(conn);
	}


	// ItemTables enum
	public enum ItemTables { Items, Auctions, Mail }
	public String ItemTableToString(ItemTables ItemTable) {
		// ItemTable Enum
		if(ItemTable == ItemTables.Items) {
			return "Items";
		} else if (ItemTable == ItemTables.Auctions) {
			return "Auctions";
		} else if (ItemTable == ItemTables.Mail) {
			return "Mail";
		}
		return null;
	}


	// encode/decode enchantments for database storage
	public static String encodeEnchantments(ItemStack stack) {
		if(stack == null) return null;
		Map<Enchantment, Integer> enchantments = stack.getEnchantments();
		if(enchantments==null || enchantments.isEmpty()) return null;
		// get enchantments
		HashMap<Integer, Integer> enchMap = new HashMap<Integer, Integer>();
		for(Map.Entry<Enchantment, Integer> entry : enchantments.entrySet()) {
			// check safe enchantments
			if(!checkSafeEnchantments(stack, entry.getKey(), entry.getValue())) continue;
			enchMap.put(entry.getKey().getId(), entry.getValue());
		}
		// sort by enchantment id
		SortedSet<Integer> enchSorted = new TreeSet<Integer> (enchMap.keySet());
		// build string
		String enchStr = "";
		for(int enchId : enchSorted) {
			int level = enchMap.get(enchId);
			if(!enchStr.isEmpty()) enchStr += ",";
			enchStr += Integer.toString(enchId)+":"+Integer.toString(level);
		}
		return enchStr;
	}
	// decode enchantments from database
	public static boolean decodeEnchantments(ItemStack stack, String enchStr) {
		if(enchStr == null || enchStr.isEmpty()) return false;
		Map<Enchantment, Integer> ench = new HashMap<Enchantment, Integer>();
		String[] parts = enchStr.split(",");
		boolean removedUnsafe = false;
		for(String part : parts) {
			if(part==null || part.isEmpty()) continue;
			String[] split = part.split(":");
			if(split.length != 2) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Invalid enchantment data found: "+part);
				continue;
			}
			int enchId = -1;
			int level  = -1;
			try {
				enchId = Integer.valueOf(split[0]);
				level  = Integer.valueOf(split[1]);
			} catch(Exception ignore) {}
			if(enchId<0 || level<1) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Invalid enchantment data found: "+part);
				continue;
			}
			Enchantment enchantment = Enchantment.getById(enchId);
			if(enchantment == null) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Invalid enchantment id found: "+part);
				continue;
			}
			// check safe enchantments
			if(!checkSafeEnchantments(stack, enchantment, level)) {
				removedUnsafe = true;
				continue;
			}
			// add enchantment to map
			ench.put(enchantment, level);
			if(removedUnsafe) WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Removed/modified unsafe enchantments!");
		}
		// add enchantments to stack
		stack.addEnchantments(ench);
		return removedUnsafe;
	}
	// check natural enchantment
	public static boolean checkSafeEnchantments(ItemStack stack, Enchantment enchantment, int level) {
		// can enchant item
		if(!enchantment.canEnchantItem(stack)) {
//			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Removed unsafe enchantment: "+stack.toString()+"  "+enchantment.toString());
			return false;
		}
		if(level < 1) return false;
		if(WebAuctionPlus.timEnabled()) {
			if(level > 127) level = 127;
		} else {
			// level to low
			if(level < enchantment.getStartLevel()) {
				level = enchantment.getStartLevel();
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Raised unsafe enchantment to level "+
					Integer.toString(level)+"  "+stack.toString()+"  "+enchantment.toString());
			}
			// level to high
			if(level > enchantment.getMaxLevel()) {
				level = enchantment.getMaxLevel();
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Lowered unsafe enchantment to level "+
						Integer.toString(level)+"  "+stack.toString()+"  "+enchantment.toString());
			}
		}
		return true;
	}


	// find existing item stack
	public int getItemStackId(String player, ItemStack stack) {
		if(stack == null) return -1;
		int itemId = stack.getTypeId();
		int itemDamage = stack.getDurability();
		String enchStr = encodeEnchantments(stack);
		if(enchStr!=null && enchStr.isEmpty()) enchStr = null;
		int keyId = -1;
		if(player == null || player.isEmpty()) return -1;
		if(itemId < 1 || itemDamage < 0) return -1;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: getItemStackId");
			st = conn.prepareStatement("SELECT `id` FROM `"+dbPrefix+"Items` WHERE "+
				"`ItemTable` = 'Items' AND "+
				"`playerName` = ? AND "+
				"`itemId` = ? AND "+
				"`itemDamage` = ? AND "+
				"`enchantments` "+(enchStr==null?"IS NULL":"= ?")+" "+
				"LIMIT 1");
			st.setString(1, player);
			st.setInt   (2, itemId);
			st.setInt   (3, itemDamage);
			if(enchStr != null)
				st.setString(4, enchStr);
			rs = st.executeQuery();
			// got stack id
			if(rs.next())
				keyId = rs.getInt("id");
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to get item stack id");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return keyId;
	}


	// add quantity
	public boolean AddItemQty(int TableItemId, int qty) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: AddItemQty " +
				Integer.toString(TableItemId) + " " + Integer.toString(qty) );
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"Items` SET `qty` = `qty` + ? WHERE `id` = ? AND `ItemTable`='Items'");
			st.setInt(1, qty);
			st.setInt(2, TableItemId);
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to update item quantity in DB");
			e.printStackTrace();
			return false;
		} finally {
			closeResources(conn, st, rs);
		}
		return true;
	}


	// add new stack to db
	public int CreateItem(String player, ItemStack stack) {
		if(stack == null) return -1;
		int itemId = stack.getTypeId();
		int itemDamage = stack.getDurability();
		int qty = stack.getAmount();
		String enchStr = encodeEnchantments(stack);
		if(enchStr!=null && enchStr.isEmpty()) enchStr = null;
		int keyId = 0;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: createItem " +
				Integer.toString(itemId)+":"+Integer.toString(itemDamage)+" x"+Integer.toString(qty) );
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"Items` "+
				"(`ItemTable`, `playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`) VALUES "+
				"('Items', ?, ?, ?, ?, "+(enchStr==null?"NULL":"?")+")",
				Statement.RETURN_GENERATED_KEYS);
			st.setString(1, player);
			st.setInt   (2, itemId);
			st.setInt   (3, itemDamage);
			st.setInt   (4, qty);
			if(enchStr != null)
				st.setString(5, enchStr);
			int affectedRows = st.executeUpdate();
			if(affectedRows == 0) throw new SQLException("Creating new wa item failed, no rows affected.");
			// get insert id
			rs = st.getGeneratedKeys();
			if(rs.next()) {
				keyId = rs.getInt(1);
				log.info(logPrefix + "Added new item; key id: "+Integer.toString(keyId)+"  "+
					Integer.toString(itemId)+":"+Integer.toString(itemDamage)+"  ench: "+enchStr );
			} else throw new SQLException("Creating new wa item failed, no generated key.");
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to create item");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return keyId;
	}


//	public List<AuctionItem> GetItems(String player, int itemID, int damage, boolean reverseOrder) {
//		List<AuctionItem> auctionItems = new ArrayList<AuctionItem>();
//		Connection conn = getConnection();
//		PreparedStatement st = null;
//		ResultSet rs = null;
//		try {
//			if(debugSQL) log.info("WA Query: getItems " + player + " " +
//				Integer.toString(itemID) + ":" + Integer.toString(damage) );
//			st = conn.prepareStatement("SELECT `id`,`itemId`,`itemDamage`,`playerName`,`qty` " +
//				"FROM `"+dbPrefix+"Items` WHERE " +
//				"`ItemTable`='Items' AND `playerName` = ? AND `itemId` = ? AND `itemDamage` = ? " +
//				"ORDER BY `id` "+(reverseOrder?"DESC":"ASC") );
//			st.setString(1, player);
//			st.setInt   (2, itemID);
//			st.setInt   (3, damage);
//			AuctionItem auctionItem;
//			rs = st.executeQuery();
//			while(rs.next()) {
//				auctionItem = new AuctionItem();
//				auctionItem.setItemId    (rs.getInt   ("id"));
//				auctionItem.setTypeId    (rs.getInt   ("itemId"));
//				auctionItem.setDamage    (rs.getInt   ("itemDamage"));
//				auctionItem.setPlayerName(rs.getString("playerName"));
//				auctionItem.setQty       (rs.getInt   ("qty"));
//				auctionItems.add(auctionItem);
//			}
//		} catch(SQLException e) {
//			log.warning(logPrefix + "Unable to get items");
//			e.printStackTrace();
//		} finally {
//			closeResources(conn, st, rs);
//		}
//		return auctionItems;
//	}


	public int hasMail(String player) {
		int mailCount = 0;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: hasMail " + player);
			st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"Items` WHERE "+
				"`ItemTable`='Mail' AND `playerName` = ?");
			st.setString(1, player);
			rs = st.executeQuery();
			if (rs.next())
				mailCount = rs.getInt("count");
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to check new mail for: " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return mailCount;
	}


	// withdraw item
	public static class MailGetter {
		public int lastKeyId = 0;
		public ItemStack getPlayerMail(String player) {
			ItemStack stack = null;
			Connection conn = WebAuctionPlus.dataQueries.getConnection();
			PreparedStatement st = null;
			ResultSet rs = null;
			try {
				if(WebAuctionPlus.dataQueries.debugSQL) log.info("WA Query: getPlayerMail " + player + " lastKeyId: " + Integer.toString(lastKeyId));
				st = conn.prepareStatement("SELECT `id`, `ItemTable`, `playerName`, `itemId`, `itemDamage`, `qty`, `enchantments` "+
					"FROM `"+WebAuctionPlus.dataQueries.dbPrefix+"Items` WHERE `ItemTable` = 'Mail' AND `playerName` = ? AND `id` > ? ORDER BY `id` ASC LIMIT 1");
				st.setString(1, player);
				st.setInt   (2, lastKeyId);
				rs = st.executeQuery();
				if(rs.next()) {
					// create item stack
					stack = new ItemStack( rs.getInt("itemId"), rs.getInt("qty"), rs.getShort("itemDamage") );
					// set enchantments
					decodeEnchantments(stack, rs.getString("enchantments"));
					lastKeyId = rs.getInt("id");
				}
			} catch(SQLException e) {
				log.warning(logPrefix+"Unable to withdraw mail for "+player);
				e.printStackTrace();
			} finally {
				WebAuctionPlus.dataQueries.closeResources(conn, st, rs);
			}
			return stack;
		}
	}


	public void deleteMail(String player, List<Integer> delMail) {
		if(delMail.size() == 0) return;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: deleteMail "+player+" "+delMail.size()+" "+delMail.toString());
			String sql  = "";
			String sql2 = "";
			int i = 0;
			for(int mailId : delMail) { i++;
				if (i!=1) {
					sql  += " OR ";
					sql2 += " OR ";
				}
				sql  += "`id`="          + Integer.toString(mailId);
				sql2 += "`ItemTableId`=" + Integer.toString(mailId);
			}
			if(sql.isEmpty() || sql2.isEmpty()) return;
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"Items` " +
				"WHERE `ItemTable` = 'Mail' AND `playerName` = ? AND ( " + sql + " ) LIMIT 36");
			st.setString(1, player);
			st.executeUpdate();
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"ItemEnchantments` " +
				"WHERE `ItemTable` = 'Mail' AND ( " + sql2 + " ) LIMIT 36");
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to remove mail " + player + " " + delMail.toString());
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


	public Map<Location, Integer> getShoutSignLocations() {
		Map<Location, Integer> signLocations = new HashMap<Location, Integer>();
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: getShoutSignLocations");
			st = conn.prepareStatement("SELECT `world`,`radius`,`x`,`y`,`z` FROM `"+dbPrefix+"ShoutSigns`");
			Location location;
			rs = st.executeQuery();
			while(rs.next()) {
				World world = Bukkit.getServer().getWorld(rs.getString("world"));
				location = new Location(world, rs.getInt("x"), rs.getInt("y"), rs.getInt("z"));
				signLocations.put(location,    rs.getInt("radius"));
			}
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to get shout sign locations");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return signLocations;
	}


	public Map<Location, Integer> getRecentSignLocations() {
		Map<Location, Integer> signLocations = new HashMap<Location, Integer>();
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: getRecentSignLocations");
			st = conn.prepareStatement("SELECT `world`,`offset`,`x`,`y`,`z` FROM `"+dbPrefix+"RecentSigns`");
			Location location;
			rs = st.executeQuery();
			while(rs.next()) {
				World world = Bukkit.getServer().getWorld(rs.getString("world"));
				location = new Location(world, rs.getInt("x"), rs.getInt("y"), rs.getInt("z"));
				signLocations.put(location,    rs.getInt("offset"));
			}
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to get shout sign locations");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return signLocations;
	}


	public Auction getAuction(int auctionId) {
		Auction auction = null;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getAuction " + Integer.toString(auctionId));
			st = conn.prepareStatement("SELECT `itemId`,`itemDamage`,`playerName`,`qty`,`price`," +
				"`allowBids`,`currentBid`,`currentWinner` FROM `WA_Auctions` WHERE `id` = ? LIMIT 1");
//UNIX_TIMESTANP(`created`) AS `created`,
			st.setInt(1, auctionId);
			rs = st.executeQuery();
			if(rs.next()) {
				auction = new Auction();
				auction.setAuctionId(auctionId);
				auction.setItemStack(new ItemStack(rs.getInt("itemId"), rs.getInt("qty"), rs.getShort("itemDamage")));
				auction.setPlayerName(rs.getString("playerName"));
				auction.setPrice(rs.getDouble("price"));
//				auction.setCreated(rs.getInt("created"));
				auction.setAllowBids(rs.getBoolean("allowBids"));
				auction.setCurrentBid(rs.getDouble("currentBid"));
				auction.setCurrentWinner(rs.getString("currentWinner"));
			}
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to get auction " + Integer.toString(auctionId));
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return auction;
	}


	public void removeShoutSign(Location location) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: removeShoutSign " + location.toString());
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"ShoutSigns` WHERE " +
				"`world` = ? AND `x` = ? AND `y` = ? AND `z` = ?");
			st.setString(1, location.getWorld().getName());
			st.setInt   (2, (int) location.getX());
			st.setInt   (3, (int) location.getY());
			st.setInt   (4, (int) location.getZ());
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to remove shout sign at location " + location);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


	public Auction getAuctionForOffset(int offset) {
		Auction auction = null;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: getAuctionForOffset " + Integer.toString(offset));
			st = conn.prepareStatement("SELECT `itemId`,`itemDamage`,`playerName`,`qty`,`price` " +
				"FROM `"+dbPrefix+"Auctions` ORDER BY `id` DESC LIMIT ?, 1");
//,UNIX_TIMESTAMP(`created`) AS `created`
			st.setInt(1, offset);
			rs = st.executeQuery();
			if(rs.next()) {
				auction = new Auction();
				auction.setAuctionId(offset);
				auction.setItemStack(new ItemStack(rs.getInt("itemId"), rs.getInt("qty"), rs.getShort("itemDamage")));
				auction.setPlayerName(rs.getString("playerName"));
				auction.setPrice(rs.getDouble("price"));
//				auction.setCreated(rs.getInt("created"));
			}
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to get auction # " + offset);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return auction;
	}


	public void removeRecentSign(Location location) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: removeRecentSign " + location.toString());
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"RecentSigns` WHERE "+
				"`world` = ? AND `x` = ? AND `y` = ? AND `z` = ?");
			st.setString(1, location.getWorld().getName());
			st.setInt   (2, (int) location.getX());
			st.setInt   (3, (int) location.getY());
			st.setInt   (4, (int) location.getZ());
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to remove recent sign at location " + location.toString());
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


	public void updatePlayerPassword(String player, String newPass) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: updatePlayerPassword " + player);
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"Players` SET `password` = ? WHERE `playerName` = ? LIMIT 1");
			st.setString(1, newPass);
			st.setString(2, player);
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to update password for player: " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


	public void createShoutSign(World world, int radius, int x, int y, int z) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: createShoutSign " +
				Integer.toString(radius) + " " + Integer.toString(x) + "," +
				Integer.toString(y) + "," + Integer.toString(z) );
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"ShoutSigns` " +
				"(`world`, `radius`, `x`, `y`, `z`) VALUES (?, ?, ?, ?, ?)");
			st.setString(1, world.getName());
			st.setInt   (2, radius);
			st.setInt   (3, x);
			st.setInt   (4, y);
			st.setInt   (5, z);
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to create shout sign");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


	public void createRecentSign(World world, int offset, int x, int y, int z) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: createRecentSign " +
				world.getName() + " " + Integer.toString(offset) + " " +
				Integer.toString(x) + "," + Integer.toString(y) + "," + Integer.toString(z) );
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"RecentSigns` " +
				"(`world`, `offset`, `x`, `y`, `z`) VALUES (?, ?, ?, ?, ?)");
			st.setString(1, world.getName());
			st.setInt   (2, offset);
			st.setInt   (3, x);
			st.setInt   (4, y);
			st.setInt   (5, z);
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to create recent sign");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


	public AuctionPlayer getPlayer(String player) {
		AuctionPlayer waPlayer = null;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: getPlayer " + player);
			st = conn.prepareStatement("SELECT `id`,`playerName`,`money`,`Permissions` " +
				"FROM `"+dbPrefix+"Players` WHERE `playerName` = ? LIMIT 1");
			st.setString(1, player);
			rs = st.executeQuery();
			if(rs.next()) {
				waPlayer = new AuctionPlayer();
				waPlayer.setPlayerId(  rs.getInt   ("id"));
				waPlayer.setPlayerName(rs.getString("playerName"));
				waPlayer.setMoney(     rs.getDouble("money"));
				waPlayer.setPerms(     rs.getString("Permissions"));
			}
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to get player " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return waPlayer;
	}


	public void updatePlayerPermissions(AuctionPlayer waPlayer, boolean canBuy, boolean canSell, boolean isAdmin) {
		// return if update not needed
		if(waPlayer.comparePerms(canBuy, canSell, isAdmin)) return;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			// update player permissions for website
			waPlayer.setPerms(canBuy, canSell, isAdmin);
			if(debugSQL) log.info("WA Query: updatePlayerPermissions " + waPlayer.getPlayerName() +
				" with perms: " + waPlayer.getPermsString());
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"Players` SET " +
				"`Permissions` = ? WHERE `playerName` = ? LIMIT 1");
			st.setString(1, waPlayer.getPermsString());
			st.setString(2, waPlayer.getPlayerName());
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to update player permissions in DB");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


	public void createPlayer(AuctionPlayer waPlayer, String pass) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: createPlayer " + waPlayer.getPlayerName() +
				" with perms: " + waPlayer.getPermsString());
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"Players` " +
				"(`playerName`, `password`, `Permissions`) VALUES (?, ?, ?)");
			st.setString(1, waPlayer.getPlayerName());
			st.setString(2, pass);
			st.setString(3, waPlayer.getPermsString());
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to update player permissions in DB");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


	public void updatePlayerMoney(String player, double money) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(debugSQL) log.info("WA Query: updatePlayerMoney " + player);
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"Players` SET `money` = ? WHERE `playerName` = ?");
			st.setDouble(1, money);
			st.setString(2, player);
			st.executeUpdate();
		} catch(SQLException e) {
			log.warning(logPrefix + "Unable to update player money in DB");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


}