package me.lorenzop.webauctionplus.mysql;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.Auction;
import me.lorenzop.webauctionplus.dao.AuctionItem;
import me.lorenzop.webauctionplus.dao.AuctionPlayer;
import me.lorenzop.webauctionplus.dao.MailItem;
import me.lorenzop.webauctionplus.dao.SaleAlert;

import org.bukkit.Location;
import org.bukkit.World;
import org.bukkit.enchantments.Enchantment;
import org.bukkit.inventory.ItemStack;

public class MySQLDataQueries extends MySQLConnPool {

	public enum ItemTables { Items, Auctions, Mail }

	protected final WebAuctionPlus plugin;

	public MySQLDataQueries(WebAuctionPlus plugin, String dbHost, int dbPort,
			String dbUser, String dbPass, String dbName, String dbPrefix) {
		this.logPrefix = WebAuctionPlus.logPrefix;
		this.plugin = plugin;
		this.dbHost = dbHost;
		this.dbPort = dbPort;
		this.dbUser = dbUser;
		this.dbPass = dbPass;
		this.dbName = dbName;
		this.dbPrefix = dbPrefix;
	}

	public String ItemTableToString(ItemTables ItemTable) {
		// ItemTable Enum
		if (ItemTable == ItemTables.Items) {
			return "Items";
		} else if (ItemTable == ItemTables.Auctions) {
			return "Auctions";
		} else if (ItemTable == ItemTables.Mail) {
			return "Mail";
		}
		return null;
	}

	public List<AuctionItem> GetItems(String player, int itemID, int damage, boolean reverseOrder) {
		List<AuctionItem> auctionItems = new ArrayList<AuctionItem>();
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getItems " + player + " " +
				Integer.toString(itemID) + ":" + Integer.toString(damage) );
			st = conn.prepareStatement("SELECT `id`,`itemId`,`itemDamage`,`playerName`,`qty` " +
				"FROM `"+dbPrefix+"Items` WHERE " +
				"`ItemTable`='Items' AND `playerName` = ? AND `itemId` = ? AND `itemDamage` = ? " +
				"ORDER BY `id` "+(reverseOrder?"DESC":"ASC") );
			st.setString(1, player);
			st.setInt(2, itemID);
			st.setInt(3, damage);
			AuctionItem auctionItem;
			rs = st.executeQuery();
			while (rs.next()) {
				auctionItem = new AuctionItem();
				auctionItem.setItemId    (rs.getInt   ("id"));
				auctionItem.setTypeId    (rs.getInt   ("itemId"));
				auctionItem.setDamage    (rs.getInt   ("itemDamage"));
				auctionItem.setPlayerName(rs.getString("playerName"));
				auctionItem.setQty       (rs.getInt   ("qty"));
				auctionItems.add(auctionItem);
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to get items");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return auctionItems;
	}

	// use to search for an existing item with the same enchantments
	public boolean ItemHasEnchantments(int itemId, Map<Enchantment, Integer> Enchantments) {
		Map<Enchantment, Integer> tempEnchantments = new HashMap<Enchantment, Integer>(Enchantments);
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		Enchantment ench;
		try {
			if (debugSQL) log.info("WA Query: ItemHasEnchantments " +
				Integer.toString(itemId) + " - Items Table " + tempEnchantments.toString());
			st = conn.prepareStatement("SELECT `enchName`, `enchId`, `level` FROM `"+dbPrefix+"ItemEnchantments` " +
				"WHERE `ItemTable` = ? AND `ItemTableId` = ? ORDER BY `enchId` DESC");
			st.setString(1, "Items");
			st.setInt   (2, itemId);
			rs = st.executeQuery();
			while (rs.next()) {
				if (debugSQL) log.info("Checking has: " + rs.getString("enchName"));
				ench = Enchantment.getByName(rs.getString("enchName"));
				if (! tempEnchantments.containsKey(ench) ) {
					if (debugSQL) log.info("Not a match");
					return false;
				}
				tempEnchantments.remove(ench);
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to get items");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		if (debugSQL && !tempEnchantments.isEmpty()) log.info("Doesn't match, has extra: " + tempEnchantments.toString());
		return tempEnchantments.isEmpty();
	}

	public void AddItemQuantity(int TableItemId, int qty) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: AddItemQuantity " +
				Integer.toString(TableItemId) + " " + Integer.toString(qty) );
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"Items` SET `qty` = `qty` + ? WHERE `id` = ? AND `ItemTable`='Items'");
			st.setInt(1, qty);
			st.setInt(2, TableItemId);
			st.executeUpdate();
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to update item quantity in DB");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public int CreateItem(String player, ItemStack stack) {
		int itemId = stack.getTypeId();
		int damage = stack.getDurability();
		int qty = stack.getAmount();
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		int keyId = 0;
		try {
			if (debugSQL) log.info("WA Query: createItem " +
				Integer.toString(itemId) + ":" + Integer.toString(damage) );
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"Items` " +
				"(`ItemTable`, `itemId`, `itemDamage`, `playerName`, `qty`) VALUES ('Items',?,?,?,?)", Statement.RETURN_GENERATED_KEYS);
			st.setInt   (1, itemId);
			st.setInt   (2, damage);
			st.setString(3, player);
			st.setInt   (4, qty);
			int affectedRows = st.executeUpdate();
			if (affectedRows == 0)
				throw new SQLException("Creating new item failed, no rows affected.");
			// get insert id
			rs = st.getGeneratedKeys();
			if (rs.next()) {
				keyId = rs.getInt(1);
				log.info(logPrefix + "Added new item " + Integer.toString(itemId) + ":" + Integer.toString(damage) +
					"  table id: " + Integer.toString(keyId));
				CreateEnchantments(keyId, stack.getEnchantments());
			} else {
				throw new SQLException("Creating new item failed, no generated key.");
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to create item");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return keyId;
	}

	private void CreateEnchantments(int ItemTableId, Map<Enchantment, Integer> Enchantments) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			for (Map.Entry<Enchantment, Integer> entry : Enchantments.entrySet()) {
				Enchantment ench = (Enchantment)entry.getKey();
				String enchName = ench.getName();
				int enchId = ench.getId();
				int level = entry.getValue();
				st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"ItemEnchantments` " +
					"(`ItemTable`,`ItemTableId`,`enchName`,`enchId`,`level`) VALUES (?, ?, ?, ?, ?)");
				// ItemTable Enum
				st.setString(1, "Items");
				// itemId
				st.setInt   (2, ItemTableId);
				// enchName
				st.setString(3, enchName);
				// enchId
				st.setInt   (4, enchId);
				// level
				st.setInt   (5, level);
				int affectedRows = st.executeUpdate();
				if (affectedRows == 0)
					throw new SQLException("Creating new item failed, no rows affected.");
				log.info(logPrefix + "Added enchantment: " + enchName + " " + Integer.toString(level));
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to create item");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public int hasMail(String player) {
		int mailCount = 0;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: hasMail " + player);
			st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"Items` WHERE `ItemTable`='Mail' AND `playerName` = ?");
			st.setString(1, player);
			rs = st.executeQuery();
			if (rs.next())
				mailCount = rs.getInt("count");
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to check new mail for: " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return mailCount;
	}


	// withdraw item
	public MailItem getMail(String player, int nextId) {
		MailItem mail = null;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getMail " + player + " nextId: " + Integer.toString(nextId));
			st = conn.prepareStatement("SELECT `id`,`itemId`,`itemDamage`,`playerName`,`qty` " +
				"FROM `"+dbPrefix+"Items` WHERE `ItemTable` = 'Mail' AND `playerName` = ? AND `id` > ? ORDER BY `id` ASC LIMIT 1");
			st.setString(1, player);
			st.setInt   (2, nextId);
			rs = st.executeQuery();
			if (rs.next()) {
				mail = new MailItem(plugin.timEnabled);
				mail.setMailId(rs.getInt("id"));
				mail.setPlayerName(player);
				mail.setItemStack(new ItemStack(rs.getInt("itemId"), rs.getInt("qty"), rs.getShort("itemDamage") ));
				getMailItemEnchantments(mail, rs.getInt("id"));
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to withdraw mail for " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return mail;
	}

	public void deleteMail(String player, List<Integer> delMail) {
		if (delMail.size() == 0) return;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: deleteMail "+player+" "+delMail.size()+" "+delMail.toString());
			String sql  = "";
			String sql2 = "";
			int i = 0;
			for (int mailId : delMail) { i++;
				if (i!=1) {
					sql  += " OR ";
					sql2 += " OR ";
				}
				sql  += "`id`="          + Integer.toString(mailId);
				sql2 += "`ItemTableId`=" + Integer.toString(mailId);
			}
			if (sql.isEmpty() || sql2.isEmpty()) return;
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"Items` " +
				"WHERE `ItemTable` = 'Mail' AND `playerName` = ? AND ( " + sql + " ) LIMIT 36");
			st.setString(1, player);
			st.executeUpdate();
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"ItemEnchantments` " +
				"WHERE `ItemTable` = 'Mail' AND ( " + sql2 + " ) LIMIT 36");
			st.executeUpdate();
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to remove mail " + player + " " + delMail.toString());
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public void getMailItemEnchantments(MailItem mail, int itemId) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getItemEnchantments - Items Table " + Integer.toString(itemId));
			st = conn.prepareStatement("SELECT `enchName`, `enchId`, `level` FROM `"+dbPrefix+"ItemEnchantments` " +
				"WHERE `ItemTable` = 'Mail' AND `ItemTableId` = ? ORDER BY `enchId` DESC");
			st.setInt   (1, itemId);
			rs = st.executeQuery();
			while (rs.next()) {
				if(debugSQL) log.info("WA Query: found ench: " + rs.getString("enchName") + " " + rs.getInt("level"));
				mail.addEnchantments(mail.getItemStack(), rs.getString("enchName"), rs.getInt("level"));
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to get items");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return;
	}

	public int getMaxAuctionID() {
		int maxAuctionID = -1;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) WebAuctionPlus.log.info("WA Query: getMaxAuctionID");
			st = conn.prepareStatement("SELECT MAX(`id`) FROM `"+dbPrefix+"Auctions`");
			rs = st.executeQuery();
			if (rs.next())
				maxAuctionID = rs.getInt(1);
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to query for max Auction ID");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return maxAuctionID;
	}

	public Map<Location, Integer> getShoutSignLocations() {
		Map<Location, Integer> signLocations = new HashMap<Location, Integer>();
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getShoutSignLocations");
			st = conn.prepareStatement("SELECT `world`,`radius`,`x`,`y`,`z` FROM `"+dbPrefix+"ShoutSigns`");
			Location location;
			rs = st.executeQuery();
			while (rs.next()) {
				World world = plugin.getServer().getWorld(rs.getString("world"));
				location = new Location(world, rs.getInt("x"), rs.getInt("y"), rs.getInt("z"));
				signLocations.put(location,    rs.getInt("radius"));
			}
		} catch (SQLException e) {
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
			if (debugSQL) log.info("WA Query: getRecentSignLocations");
			st = conn.prepareStatement("SELECT `world`,`offset`,`x`,`y`,`z` FROM `"+dbPrefix+"RecentSigns`");
			Location location;
			rs = st.executeQuery();
			while (rs.next()) {
				World world = plugin.getServer().getWorld(rs.getString("world"));
				location = new Location(world, rs.getInt("x"), rs.getInt("y"), rs.getInt("z"));
				signLocations.put(location,    rs.getInt("offset"));
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to get shout sign locations");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return signLocations;
	}

	public List<SaleAlert> getNewSaleAlertsForSeller(String player) {
		List<SaleAlert> saleAlerts = new ArrayList<SaleAlert>();
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getNewSaleAlertsForSeller " + player);
			st = conn.prepareStatement("SELECT `id`,`seller`,`qty`,`price`,`buyer`,`item` FROM `" +
				dbPrefix+"SaleAlerts` WHERE `seller` = ? AND `alerted` = 0");
			st.setString(1, player);
			SaleAlert saleAlert;
			rs = st.executeQuery();
			while (rs.next()) {
				saleAlert = new SaleAlert();
				saleAlert.setAlertId  (rs.getInt   ("id"));
				saleAlert.setBuyerName(rs.getString("buyer"));
				saleAlert.setItem     (rs.getString("item"));
				saleAlert.setQty      (rs.getInt   ("qty"));
				saleAlert.setPriceEach(rs.getDouble("price"));
				saleAlerts.add(saleAlert);
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to get sale alerts for player " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return saleAlerts;
	}

	public void markSaleAlertSeen(int id) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: markSaleAlertSeen " + Integer.toString(id));
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"SaleAlerts` SET `alerted` = 1 WHERE `id` = ?");
			st.setInt(1, id);
			st.executeUpdate();
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to mark sale alert seen " + id);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public Auction getAuction(int auctionId) {
		Auction auction = null;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getAuction " + Integer.toString(auctionId));
			st = conn.prepareStatement("SELECT `itemId`,`itemDamage`,`playerName`,`qty`,`price`," +
				"`allowBids`,`currentBid`,`currentWinner` FROM `WA_Auctions` WHERE `id` = ?");
//UNIX_TIMESTANP(`created`) AS `created`,
			st.setInt(1, auctionId);
			rs = st.executeQuery();
			while (rs.next()) {
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
		} catch (SQLException e) {
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
			if (debugSQL) log.info("WA Query: removeShoutSign " + location.toString());
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"ShoutSigns` WHERE " +
				"`world` = ? AND `x` = ? AND `y` = ? AND `z` = ?");
			st.setString(1, location.getWorld().getName());
			st.setInt(2, (int) location.getX());
			st.setInt(3, (int) location.getY());
			st.setInt(4, (int) location.getZ());
			st.executeUpdate();
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to remove shout sign at location " + location);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

// TODO: add caching with TTL
	public int getTotalAuctionCount() {
		int totalAuctionCount = 0;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getTotalAuctionCount");
			st = conn.prepareStatement("SELECT COUNT(*) FROM `"+dbPrefix+"Auctions`");
			rs = st.executeQuery();
			if (rs.next())
				totalAuctionCount = rs.getInt(1);
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to get total auction count");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return totalAuctionCount;
	}

	public Auction getAuctionForOffset(int offset) {
		Auction auction = null;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (debugSQL) log.info("WA Query: getAuctionForOffset " + Integer.toString(offset));
			st = conn.prepareStatement("SELECT `itemId`,`itemDamage`,`playerName`,`qty`,`price` " +
				"FROM `"+dbPrefix+"Auctions` ORDER BY `id` DESC LIMIT ?, 1");
//,UNIX_TIMESTAMP(`created`) AS `created`
			st.setInt(1, offset);
			rs = st.executeQuery();
			while (rs.next()) {
				auction = new Auction();
				auction.setAuctionId(offset);
				auction.setItemStack(new ItemStack(rs.getInt("itemId"), rs.getInt("qty"), rs.getShort("itemDamage")));
				auction.setPlayerName(rs.getString("playerName"));
				auction.setPrice(rs.getDouble("price"));
//				auction.setCreated(rs.getInt("created"));
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to get auction " + offset);
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
			if (debugSQL) log.info("WA Query: removeRecentSign " + location.toString());
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"RecentSigns` WHERE " +
				"`world` = ? AND `x` = ? AND `y` = ? AND `z` = ?");
			st.setString(1, location.getWorld().getName());
			st.setInt(2, (int) location.getX());
			st.setInt(3, (int) location.getY());
			st.setInt(4, (int) location.getZ());
			st.executeUpdate();
		} catch (SQLException e) {
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
			if (debugSQL) log.info("WA Query: updatePlayerPassword " + player);
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"Players` SET `password` = ? WHERE `playerName` = ? LIMIT 1");
			st.setString(1, newPass);
			st.setString(2, player);
			st.executeUpdate();
		} catch (SQLException e) {
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
			if (debugSQL) log.info("WA Query: createShoutSign " +
				Integer.toString(radius) + " " + Integer.toString(x) + "," +
				Integer.toString(y) + "," + Integer.toString(z) );
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"ShoutSigns` " +
				"(`world`, `radius`, `x`, `y`, `z`) VALUES (?, ?, ?, ?, ?)");
			st.setString(1, world.getName());
			st.setInt(2, radius);
			st.setInt(3, x);
			st.setInt(4, y);
			st.setInt(5, z);
			st.executeUpdate();
		} catch (SQLException e) {
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
			if (debugSQL) log.info("WA Query: createRecentSign " +
				world.getName() + " " + Integer.toString(offset) + " " +
				Integer.toString(x) + "," + Integer.toString(y) + "," + Integer.toString(z) );
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"RecentSigns` " +
				"(`world`, `offset`, `x`, `y`, `z`) VALUES (?, ?, ?, ?, ?)");
			st.setString(1, world.getName());
			st.setInt(2, offset);
			st.setInt(3, x);
			st.setInt(4, y);
			st.setInt(5, z);
			st.executeUpdate();
		} catch (SQLException e) {
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
			if (debugSQL) log.info("WA Query: getPlayer " + player);
			st = conn.prepareStatement("SELECT `id`,`playerName`,`money`,`Permissions` " +
				"FROM `"+dbPrefix+"Players` WHERE `playerName` = ? LIMIT 1");
			st.setString(1, player);
			rs = st.executeQuery();
			if (rs.next()) {
				waPlayer = new AuctionPlayer();
				waPlayer.setPlayerId(  rs.getInt   ("id"));
				waPlayer.setPlayerName(rs.getString("playerName"));
				waPlayer.setMoney(     rs.getDouble("money"));
				waPlayer.setPerms(     rs.getString("Permissions"));
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to get player " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return waPlayer;
	}

	public void updatePlayerPermissions(AuctionPlayer waPlayer, boolean canBuy, boolean canSell, boolean isAdmin) {
		// return if update not needed
		if (Boolean.valueOf( canBuy  ).equals( waPlayer.getCanBuy()  ) &&
			Boolean.valueOf( canSell ).equals( waPlayer.getCanSell() ) &&
			Boolean.valueOf( isAdmin ).equals( waPlayer.getIsAdmin() ) ) {
			return;
		}
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			waPlayer.setPerms(canBuy, canSell, isAdmin);
			if (debugSQL) log.info("WA Query: updatePlayerPermissions " + waPlayer.getPlayerName() +
				" with perms: " + waPlayer.getPermsString());
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"Players` SET " +
				"`Permissions` = ? WHERE `playerName` = ? LIMIT 1");
			st.setString(1, waPlayer.getPermsString());
			st.setString(2, waPlayer.getPlayerName());
			st.executeUpdate();
		} catch (SQLException e) {
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
			if (debugSQL) log.info("WA Query: createPlayer " + waPlayer.getPlayerName() +
				" with perms: " + waPlayer.getPermsString());
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"Players` " +
				"(`playerName`, `password`, `Permissions`) VALUES (?, ?, ?)");
			st.setString(1, waPlayer.getPlayerName());
			st.setString(2, pass);
			st.setString(3, waPlayer.getPermsString());
			st.executeUpdate();
		} catch (SQLException e) {
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
			if (debugSQL) log.info("WA Query: updatePlayerMoney " + player);
			st = conn.prepareStatement("UPDATE `"+dbPrefix+"Players` SET `money` = ? WHERE `playerName` = ?");
			st.setDouble(1, money);
			st.setString(2, player);
			st.executeUpdate();
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to update player money in DB");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

}