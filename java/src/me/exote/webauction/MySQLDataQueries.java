package me.exote.webauction;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import me.exote.webauction.dao.Auction;
import me.exote.webauction.dao.AuctionItem;
import me.exote.webauction.dao.AuctionMail;
import me.exote.webauction.dao.AuctionPlayer;
import me.exote.webauction.dao.SaleAlert;

import org.bukkit.Location;
import org.bukkit.World;
import org.bukkit.inventory.ItemStack;

public class MySQLDataQueries {

	private WebAuction plugin;
	private String dbHost;
	private String dbPort;
	private String dbUser;
	private String dbPass;
	private String dbName;

	public MySQLDataQueries(WebAuction plugin, String dbHost, String dbPort, String dbUser, String dbPass, String dbName) {
		this.plugin = plugin;
		this.dbHost = dbHost;
		this.dbPort = dbPort;
		this.dbUser = dbUser;
		this.dbPass = dbPass;
		this.dbName = dbName;
	}

	private Connection getConnection() {
		try {
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			return DriverManager.getConnection("jdbc:mysql://" + dbHost + ":" + dbPort + "/" + dbName, dbUser, dbPass);
		} catch (Exception e) {
			plugin.log.severe(plugin.logPrefix + "Exception getting mySQL Connection");
			e.printStackTrace();
		}
		return null;
	}

	private void closeResources(Connection conn, Statement st, ResultSet rs) {
		if (null != rs) {
			try {
				rs.close();
			} catch (SQLException e) {
			}
		}
		if (null != st) {
			try {
				st.close();
			} catch (SQLException e) {
			}
		}
		if (null != conn) {
			try {
				conn.close();
			} catch (SQLException e) {
			}
		}
	}

	private boolean tableExists(String tableName) {
		boolean exists = false;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SHOW TABLES LIKE ?");
			st.setString(1, tableName);
			rs = st.executeQuery();
			while (rs.next()) {
				exists = true;
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to check if table exists: " + tableName);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return exists;
	}

	private void executeRawSQL(String sql) {
		Connection conn = getConnection();
		Statement st = null;
		ResultSet rs = null;

		try {
			st = conn.createStatement();
			st.executeUpdate(sql);
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Exception executing raw SQL" + sql);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public void initTables() {
		if (!tableExists("WA_Players")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_Players");
			executeRawSQL("CREATE TABLE WA_Players (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(255), pass VARCHAR(255), money DOUBLE, itemsSold INT, itemsBought INT, earnt DOUBLE, spent DOUBLE, canBuy INT, canSell INT, isAdmin INT);");
		}
		if (!tableExists("WA_StorageCheck")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_StorageCheck");
			executeRawSQL("CREATE TABLE WA_StorageCheck (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), time INT);");
		}
		if (!tableExists("WA_Items")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_Items");
			executeRawSQL("CREATE TABLE WA_Items (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), name INT, damage INT, player VARCHAR(255), quantity INT);");
		}
		if (!tableExists("WA_Enchantments")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_Enchantments");
			executeRawSQL("CREATE TABLE WA_Enchantments (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), enchName VARCHAR(255), enchId INT, level INT);");
		}
		if (!tableExists("WA_EnchantLinks")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_EnchantLinks");
			executeRawSQL("CREATE TABLE WA_EnchantLinks (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), enchId INT, itemTableId INT, itemId INT);");
		}
		if (!tableExists("WA_Auctions")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_Auctions");
			executeRawSQL("CREATE TABLE WA_Auctions (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), name INT, damage INT, player VARCHAR(255), quantity INT, price DOUBLE, created INT, allowBids BOOLEAN Default '0', currentBid DOUBLE, currentWinner VARCHAR(255));");
		}
		if (!tableExists("WA_SellPrice")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_SellPrice");
			executeRawSQL("CREATE TABLE WA_SellPrice (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), name INT, damage INT, time INT, quantity INT, price DOUBLE, seller VARCHAR(255), buyer VARCHAR(255));");
		}
		if (!tableExists("WA_MarketPrices")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_MarketPrices");
			executeRawSQL("CREATE TABLE WA_MarketPrices (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), name INT, damage INT, time INT, marketprice DOUBLE, ref INT);");
		}
		if (!tableExists("WA_Mail")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_Mail");
			executeRawSQL("CREATE TABLE WA_Mail (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), name INT, damage INT, player VARCHAR(255), quantity INT);");
		}
		if (!tableExists("WA_RecentSigns")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_RecentSigns");
			executeRawSQL("CREATE TABLE WA_RecentSigns (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), world VARCHAR(255), offset INT, x INT, y INT, z INT);");
		}
		if (!tableExists("WA_ShoutSigns")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_ShoutSigns");
			executeRawSQL("CREATE TABLE WA_ShoutSigns (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), world VARCHAR(255), radius INT, x INT, y INT, z INT);");
		}
		if (!tableExists("WA_SaleAlerts")) {
			plugin.log.info(plugin.logPrefix + "Creating table WA_SaleAlerts");
			executeRawSQL("CREATE TABLE WA_SaleAlerts (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), seller VARCHAR(255), quantity INT, price DOUBLE, buyer VARCHAR(255), item VARCHAR(255), alerted BOOLEAN Default '0');");
		}
	}

	public int getMaxAuctionID() {
		int maxAuctionID = -1;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT MAX(id) FROM WA_Auctions");
			rs = st.executeQuery();
			while (rs.next()) {
				maxAuctionID = rs.getInt(1);
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to query for max Auction ID");
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
			st = conn.prepareStatement("SELECT * FROM WA_ShoutSigns");
			Location location;
			rs = st.executeQuery();
			while (rs.next()) {
				World world = plugin.getServer().getWorld(rs.getString(2));
				location = new Location(world, rs.getInt("x"), rs.getInt("y"), rs.getInt("z"));
				signLocations.put(location, rs.getInt("radius"));
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get shout sign locations");
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
			st = conn.prepareStatement("SELECT * FROM WA_RecentSigns");
			Location location;
			rs = st.executeQuery();
			while (rs.next()) {
				World world = plugin.getServer().getWorld(rs.getString(2));
				location = new Location(world, rs.getInt("x"), rs.getInt("y"), rs.getInt("z"));
				signLocations.put(location, rs.getInt("offset"));
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get shout sign locations");
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
			st = conn.prepareStatement("SELECT * FROM WA_SaleAlerts WHERE seller = ? AND alerted = ?");
			st.setString(1, player);
			st.setInt(2, 0);
			SaleAlert saleAlert;
			rs = st.executeQuery();
			while (rs.next()) {
				saleAlert = new SaleAlert();
				saleAlert.setId(rs.getInt("id"));
				saleAlert.setBuyer(rs.getString("buyer"));
				saleAlert.setItem(rs.getString("item"));
				saleAlert.setQuantity(rs.getInt("quantity"));
				saleAlert.setPriceEach(rs.getDouble("price"));
				saleAlerts.add(saleAlert);
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get sale alerts for player " + player);
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
			st = conn.prepareStatement("UPDATE WA_SaleAlerts SET alerted = ? WHERE id = ?");
			st.setInt(1, 1);
			st.setInt(2, id);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to mark sale alert seen " + id);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public Auction getAuction(int id) {
		Auction auction = null;

		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT * FROM WA_Auctions WHERE id = ?");
			st.setInt(1, id);
			rs = st.executeQuery();
			while (rs.next()) {
				auction = new Auction();
				auction.setId(id);
				auction.setItemStack(new ItemStack(rs.getInt("name"), rs.getInt("quantity"), rs.getShort("damage")));
				auction.setPlayerName(rs.getString("player"));
				auction.setPrice(rs.getDouble("price"));
				auction.setCreated(rs.getInt("created"));
				auction.setAllowBids(rs.getBoolean("allowBids"));
				auction.setCurrentBid(rs.getDouble("currentBid"));
				auction.setCurrentWinner(rs.getString("currentWinner"));
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get auction " + id);
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
			st = conn.prepareStatement("DELETE FROM WA_ShoutSigns WHERE world = ? AND x = ? AND y = ? AND z = ?");
			st.setString(1, location.getWorld().getName());
			st.setInt(2, (int) location.getX());
			st.setInt(3, (int) location.getY());
			st.setInt(4, (int) location.getZ());
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to remove shout sign at location " + location);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public int getTotalAuctionCount() {
		int totalAuctionCount = 0;

		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT COUNT(*) FROM WA_Auctions");
			rs = st.executeQuery();
			while (rs.next()) {
				totalAuctionCount = rs.getInt(1);
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get total auction count");
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
			st = conn.prepareStatement("SELECT * FROM WA_Auctions ORDER BY id DESC LIMIT ?, 1");
			st.setInt(1, offset);
			rs = st.executeQuery();
			while (rs.next()) {
				auction = new Auction();
				auction.setId(offset);
				auction.setItemStack(new ItemStack(rs.getInt("name"), rs.getInt("quantity"), rs.getShort("damage")));
				auction.setPlayerName(rs.getString("player"));
				auction.setPrice(rs.getDouble("price"));
				auction.setCreated(rs.getInt("created"));
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get auction " + offset);
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
			st = conn.prepareStatement("DELETE FROM WA_RecentSigns WHERE world = ? AND x = ? AND y = ? AND z = ?");
			st.setString(1, location.getWorld().getName());
			st.setInt(2, (int) location.getX());
			st.setInt(3, (int) location.getY());
			st.setInt(4, (int) location.getZ());
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to remove recent sign at location " + location);
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
			st = conn.prepareStatement("UPDATE WA_Players SET pass = ? WHERE name = ?");
			st.setString(1, newPass);
			st.setString(2, player);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to update password for player: " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public void createShoutSign(World world, int raidus, int x, int y, int z) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("INSERT INTO WA_ShoutSigns (world, radius, x, y, z) VALUES (?, ?, ?, ?, ?)");
			st.setString(1, world.getName());
			st.setInt(2, raidus);
			st.setInt(3, x);
			st.setInt(4, y);
			st.setInt(5, z);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to create shout sign");
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
			st = conn.prepareStatement("INSERT INTO WA_RecentSigns (world, offset, x, y, z) VALUES (?, ?, ?, ?, ?)");
			st.setString(1, world.getName());
			st.setInt(2, offset);
			st.setInt(3, x);
			st.setInt(4, y);
			st.setInt(5, z);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to create recent sign");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public boolean hasMail(String player) {
		boolean exists = false;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT * FROM WA_Mail WHERE name = ?");
			st.setString(1, player);
			rs = st.executeQuery();
			while (rs.next()) {
				exists = true;
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to check new mail for: " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return exists;
	}

	public AuctionPlayer getPlayer(String player) {
		AuctionPlayer waPlayer = null;

		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT * FROM WA_Players WHERE name = ?");
			st.setString(1, player);
			rs = st.executeQuery();
			while (rs.next()) {
				waPlayer = new AuctionPlayer();
				waPlayer.setId(rs.getInt("id"));
				waPlayer.setName(rs.getString("name"));
				waPlayer.setPass(rs.getString("pass"));
				waPlayer.setMoney(rs.getDouble("money"));
				waPlayer.setCanBuy(rs.getInt("canBuy"));
				waPlayer.setCanSell(rs.getInt("canSell"));
				waPlayer.setIsAdmin(rs.getInt("isAdmin"));
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get player " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return waPlayer;
	}

	public void updatePlayerPermissions(String player, int canBuy, int canSell, int isAdmin) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("UPDATE WA_Players SET canBuy = ?, canSell = ?, isAdmin = ? WHERE name = ?");
			st.setInt(1, canBuy);
			st.setInt(2, canSell);
			st.setInt(3, isAdmin);
			st.setString(4, player);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to update player permissions in DB");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public void createPlayer(String player, String pass, double money, int canBuy, int canSell, int isAdmin) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("INSERT INTO WA_Players (name, pass, money, canBuy, canSell, isAdmin) VALUES (?, ?, ?, ?, ?, ?)");
			st.setString(1, player);
			st.setString(2, pass);
			st.setDouble(3, money);
			st.setInt(4, canBuy);
			st.setInt(5, canSell);
			st.setInt(6, isAdmin);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to update player permissions in DB");
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
			st = conn.prepareStatement("UPDATE WA_Players SET money = ? WHERE name = ?");
			st.setDouble(1, money);
			st.setString(2, player);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to update player money in DB");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public List<AuctionItem> getItems(String player, int itemID, int damage, boolean reverseOrder) {
		List<AuctionItem> auctionItems = new ArrayList<AuctionItem>();

		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			String sql = "SELECT * FROM WA_Items WHERE player = ? AND name = ? AND damage = ?";
			if (reverseOrder) {
				sql += " ORDER BY id DESC";
			}
			st = conn.prepareStatement(sql);
			st.setString(1, player);
			st.setInt(2, itemID);
			st.setInt(3, damage);
			AuctionItem auctionItem;
			rs = st.executeQuery();
			while (rs.next()) {
				auctionItem = new AuctionItem();
				auctionItem.setId(rs.getInt("id"));
				auctionItem.setName(rs.getInt("name"));
				auctionItem.setDamage(rs.getInt("damage"));
				auctionItem.setPlayerName(rs.getString("player"));
				auctionItem.setQuantity(rs.getInt("quantity"));
				auctionItems.add(auctionItem);
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get items");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return auctionItems;
	}

	public int getEnchantTableID(int enchantID, int level, String enchantName) {
		int tableID = -1;

		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT * FROM WA_Enchantments WHERE enchId = ? AND level = ? AND enchName = ?");
			st.setInt(1, enchantID);
			st.setInt(2, level);
			st.setString(3, enchantName);
			rs = st.executeQuery();
			while (rs.next()) {
				tableID = rs.getInt("id");
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get items");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return tableID;
	}

	public void createEnchantment(String enchantName, int enchantID, int level) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("INSERT INTO WA_Enchantments (enchName, enchId, level) VALUES (?, ?, ?)");
			st.setString(1, enchantName);
			st.setInt(2, enchantID);
			st.setInt(3, level);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to create enchantment");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public List<Integer> getEnchantIDsForLinks(int itemID, int itemTableID) {
		List<Integer> enchantIDs = new ArrayList<Integer>();

		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT * FROM WA_EnchantLinks WHERE itemTableId = ? AND itemId = ? ORDER BY enchId DESC");
			st.setInt(1, itemTableID);
			st.setInt(2, itemID);
			rs = st.executeQuery();
			while (rs.next()) {
				enchantIDs.add(rs.getInt("enchId"));
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get items");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return enchantIDs;
	}

	public void updateItemQuantity(int quantity, int id) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("UPDATE WA_Items SET quantity = ? WHERE id = ?");
			st.setInt(1, quantity);
			st.setInt(2, id);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to update item quantity in DB");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public void createItem(int itemID, int itemDamage, String player, int quantity) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("INSERT INTO WA_Items (name, damage, player, quantity) VALUES (?, ?, ?, ?)");
			st.setInt(1, itemID);
			st.setInt(2, itemDamage);
			st.setString(3, player);
			st.setInt(4, quantity);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to create item");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public void createEnchantLink(int enchantID, int itemTableID, int itemID) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("INSERT INTO WA_EnchantLinks (enchId, itemTableId, itemId) VALUES (?, ?, ?)");
			st.setInt(1, enchantID);
			st.setInt(2, itemTableID);
			st.setInt(3, itemID);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to create item");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	public List<AuctionMail> getMail(String player) {
		List<AuctionMail> auctionMails = new ArrayList<AuctionMail>();

		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT * FROM WA_Mail WHERE player = ?");
			st.setString(1, player);
			AuctionMail auctionMail;
			rs = st.executeQuery();
			while (rs.next()) {
				auctionMail = new AuctionMail();
				auctionMail.setId(rs.getInt("id"));
				ItemStack stack = new ItemStack(rs.getInt("name"), rs.getInt("quantity"), rs.getShort("damage"));
				auctionMail.setItemStack(stack);
				auctionMail.setPlayerName(rs.getString("player"));
				auctionMails.add(auctionMail);
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get mail for player " + player);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return auctionMails;
	}

	public Map<Integer, Integer> getEnchantIDLevel(int id) {
		Map<Integer, Integer> returnInfo = new HashMap<Integer, Integer>();

		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("SELECT * FROM WA_Enchantments WHERE id = ?");
			st.setInt(1, id);
			rs = st.executeQuery();
			while (rs.next()) {
				returnInfo.put(rs.getInt("enchId"), rs.getInt("level"));
			}
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to get items");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return returnInfo;
	}

	public void deleteMail(int id) {
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;

		try {
			st = conn.prepareStatement("DELETE FROM WA_Mail WHERE id = ?");
			st.setInt(1, id);
			st.executeUpdate();
		} catch (SQLException e) {
			plugin.log.warning(plugin.logPrefix + "Unable to remove mail " + id);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

}