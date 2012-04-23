package me.lorenzop.webauctionplus;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

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

	public boolean debugSQL = false;

	protected final WebAuctionPlus plugin;
	public MySQLDataQueries(WebAuctionPlus plugin, String dbHost, String dbPort,
			String dbUser, String dbPass, String dbName, String dbPrefix) {
		MySQLConnPool.logPrefix = WebAuctionPlus.logPrefix;
		this.plugin = plugin;
		this.dbHost = dbHost;
		this.dbPort = dbPort;
		this.dbUser = dbUser;
		this.dbPass = dbPass;
		this.dbName = dbName;
		this.dbPrefix = dbPrefix;
	}

	public void initTables() {
		boolean convertFromWAOriginal = false;
		
		// create new tables
		if (tableExists("Auctions")) {
			if (!tableExists("ItemEnchantments"))
				convertFromWAOriginal = true;
		} else {
			setTableExists("Auctions",
				"`id`           INT(11)   NOT NULL AUTO_INCREMENT  , PRIMARY KEY(`id`), " +
				"`playerName` VARCHAR(16)     NULL DEFAULT NULL    , " +
				"`itemId`       INT(11)   NOT NULL DEFAULT '0'     , " +
				"`itemDamage`   INT(11)   NOT NULL DEFAULT '0'     , " +
				"`qty`          INT(11)   NOT NULL DEFAULT '0'     , " +
				"`price`     DOUBLE(11,2) NOT NULL DEFAULT '0.00'  , " +
				"`created`  DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00', " +
				"`allowBids` TINYINT(1)   NOT NULL DEFAULT '0'     , " +
				"`currentBid` DOUBLE(11,2) NOT NULL DEFAULT '0.00' , " +
				"`currentWinner` VARCHAR(16)  NULL DEFAULT NULL      ");
			setTableExists("Players",
				"`id`           INT(11)   NOT NULL AUTO_INCREMENT  , PRIMARY KEY(`id`), " +
				"`playerName` VARCHAR(16)     NULL DEFAULT NULL    , " +
				"`password` VARCHAR(32)       NULL DEFAULT NULL    , " +
				"`money`     DOUBLE(11,2) NOT NULL DEFAULT '0.00'  , " +
				"`itemsSold`    INT(11)   NOT NULL DEFAULT '0'     , " +
				"`itemsBought`  INT(11)   NOT NULL DEFAULT '0'     , " +
				"`earnt`     DOUBLE(11,2) NOT NULL DEFAULT '0.00'  , " +
				"`spent`     DOUBLE(11,2) NOT NULL DEFAULT '0.00'  , " +
				"`Permissions` SET( 'canBuy', 'canSell', 'isAdmin' ) NOT NULL ");
			setTableExists("Items",
				"`id`           INT(11)   NOT NULL AUTO_INCREMENT  , PRIMARY KEY(`id`), " +
				"`ItemTable`   ENUM('Items','Mail') NULL DEFAULT NULL, " +
				"`playerName` VARCHAR(16)     NULL DEFAULT NULL    , " +
				"`itemId`       INT(11)   NOT NULL DEFAULT '0'     , " +
				"`itemDamage`   INT(11)   NOT NULL DEFAULT '0'     , " +
				"`qty`          INT(11)   NOT NULL DEFAULT '0'       ");
			setTableExists("SellPrice",
				"`id`           INT(11)   NOT NULL AUTO_INCREMENT  , PRIMARY KEY(`id`), " +
				"`itemId`       INT(11)   NOT NULL DEFAULT '0'     , " +
				"`itemDamage`   INT(11)   NOT NULL DEFAULT '0'     , " +
				"`time`    DATETIME       NOT NULL DEFAULT '0000-00-00 00:00:00', " +
				"`qty`          INT(11)   NOT NULL DEFAULT '0'     , " +
				"`price`     DOUBLE(11,2) NOT NULL DEFAULT '0.00'  , " +
				"`seller`   VARCHAR(16)       NULL DEFAULT NULL    , " +
				"`buyer`    VARCHAR(16)       NULL DEFAULT NULL      ");
			setTableExists("MarketPrices",
				"`id`           INT(11)   NOT NULL AUTO_INCREMENT  , PRIMARY KEY(`id`), " +
				"`itemId`       INT(11)   NOT NULL DEFAULT '0'     , " +
				"`itemDamage`   INT(11)   NOT NULL DEFAULT '0'     , " +
				"`time`    DATETIME       NOT NULL DEFAULT '0000-00-00 00:00:00', " +
				"`marketprice` DOUBLE(11,2) NOT NULL DEFAULT '0.00', " +
				"`ref`          INT(11)   NOT NULL DEFAULT '0'       ");
			setTableExists("RecentSigns",
				"`id`           INT(11)   NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), " +
				"`world`    VARCHAR(32)       NULL DEFAULT NULL    , " +
				"`offset`       INT(11)   NOT NULL DEFAULT '0'     , " +
				"`x`            INT(11)   NOT NULL DEFAULT '0'     , " +
				"`y`            INT(11)   NOT NULL DEFAULT '0'     , " +
				"`z`            INT(11)   NOT NULL DEFAULT '0'       ");
			setTableExists("ShoutSigns",
				"`id`           INT(11)   NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), " +
				"`world`    VARCHAR(32)       NULL DEFAULT NULL    , " +
				"`radius`       INT(11)   NOT NULL DEFAULT '0'     , " +
				"`x`            INT(11)   NOT NULL DEFAULT '0'     , " +
				"`y`            INT(11)   NOT NULL DEFAULT '0'     , " +
				"`z`            INT(11)   NOT NULL DEFAULT '0'       ");
			setTableExists("SaleAlerts",
				"`id`      INT(11)   NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), " +
				"`seller`   VARCHAR(16)       NULL DEFAULT NULL    , " +
				"`qty`          INT(11)   NOT NULL DEFAULT '0'     , " +
				"`price`     DOUBLE(11,2) NOT NULL DEFAULT '0.00'  , " +
				"`buyer`    VARCHAR(16)       NULL DEFAULT NULL    , " +
				"`item`     VARCHAR(16)       NULL DEFAULT NULL    , " +
				"`alerted`  TINYINT(1)    NOT NULL DEFAULT '0'       ");
		}
		// new tables in plus
		setTableExists("ItemEnchantments",
			"`id`           INT(11)   NOT NULL AUTO_INCREMENT  , PRIMARY KEY(`id`), " +
			"`ItemTable`   ENUM('Items','Auctions','Mail') NULL DEFAULT NULL, " +
			"`ItemTableId`  INT(11)   NOT NULL DEFAULT '0'     , " +
			"`enchName` VARCHAR(32)       NULL DEFAULT NULL    , " +
			"`enchId`       INT(11)   NOT NULL DEFAULT '0'     , " +
			"`level`    TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' ");
		setTableExists("Settings",
			"`id`          INT(11)    NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), " +
			"`name`    VARCHAR(32)        NULL DEFAULT NULL  , UNIQUE(`name`)   , " +
			"`value`   VARCHAR(255)       NULL DEFAULT NULL      ");
		if (convertFromWAOriginal) {
			// convert database tables to Plus
			log.warning(logPrefix + "Converting database to Plus...");
			ConvertDatabase();
			log.warning(logPrefix + "Finished converting database to Plus!");
			log.warning(logPrefix + "*** You can delete these tables from the database: EnchantLinks, Enchantments, Mail ***");

		}
	}

	// convert database tables to Plus
	private void ConvertDatabase() {
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`name`		`itemId`	INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`damage`	`itemDamage` INT(11)		NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`player`	`playerName` VARCHAR(16)	CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`quantity`	`qty`		INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`price`		`price`		DOUBLE(11,2)	NOT NULL	DEFAULT '0.00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`created`	`created`	DATETIME		NOT NULL	DEFAULT '0000-00-00 00:00:00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`allowBids`	`allowBids`	TINYINT(1)		NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`currentBid` `currentBid` DOUBLE(11,2)	NOT NULL	DEFAULT '0.00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`currentWinner` `currentWinner` VARCHAR(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`name`		`playerName` VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`pass`		`password`	VARCHAR(32)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`money`		`money`		DOUBLE(11,2) 	NOT NULL	DEFAULT '0.00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`itemsSold`   `itemsSold`	INT(11)		NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`itemsBought` `itemsBought`	INT(11)		NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`earnt`		`earnt`		DOUBLE(11,2) 	NOT NULL	DEFAULT '0.00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	ADD			`Permissions` SET( 'canBuy', 'canSell', 'isAdmin' )	NOT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`id`		`id`		INT(11)         NOT NULL    AUTO_INCREMENT");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		ADD			`ItemTable`	ENUM('Items','Mail')		NULL		DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`player`	`playerName` VARCHAR(16)	CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`name`		`itemId`	INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`damage`	`itemDamage` INT(11)		NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`quantity`	`qty`		INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`name`		`itemId`	INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`damage`	`itemDamage` INT(11)		NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`time`		`time`		DATETIME		NOT NULL	DEFAULT '0000-00-00 00:00:00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`quantity`	`qty`		INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`price`		`price`		DOUBLE(11,2)	NOT NULL	DEFAULT '0.00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`seller`	`seller`	VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`buyer`		`buyer`		VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`name`		`itemId`	INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`damage`	`itemDamage` INT(11)		NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`time`		`time`		DATETIME		NOT NULL	DEFAULT '0000-00-00 00:00:00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`marketprice` `marketprice` DOUBLE(11,2) NOT NULL	DEFAULT '0.00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`ref`		`ref`		INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`world`		`world`		VARCHAR(32)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`offset`	`offset`	INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`x`			`x`			INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`y`			`y`			INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`z`			`z`			INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`world`		`world`		VARCHAR(32)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`radius`	`radius`	INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`x`			`x`			INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`y`			`y`			INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`z`			`z`			INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts` CHANGE		`id`         `alertId`   INT(11)         NOT NULL    AUTO_INCREMENT");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`seller`	`seller`	VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`quantity`	`qty`		INT(11)			NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`price`		`price`		DOUBLE(11,2)	NOT	NULL	DEFAULT '0.00'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`buyer`		`buyer`		VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`item`		`item`		VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`alerted`	`alerted`	TINYINT(1)		NOT NULL	DEFAULT '0'");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Settings	CHANGE		`name`		`name`		VARCHAR(32)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL	UNIQUE(`name`)");
		executeRawSQL("ALTER TABLE `"+dbPrefix+"Settings	CHANGE		`value`		`value`		VARCHAR(255)	CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
		// convert data to new tables
		Connection conn = null;
		PreparedStatement st    = null;
		PreparedStatement stNew = null;
		ResultSet rs    = null;
		ResultSet rs2   = null;
		// update player permissions
		if (tableExists("Players")) {
			int countPlayers = 0;
			int totalPlayers = 0;
			conn = getConnection();
			st    = null;
			stNew = null;
			rs    = null;
			rs2   = null;
			try {
				if (debugSQL) log.info("WA Query: Convert Database Players");
				// get total players
				st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"Players`");
				rs = st.executeQuery();
				if (!rs.next()) {
					log.severe(logPrefix + "Could not get total players!");
					return;
				}
				totalPlayers = rs.getInt(1);
				log.info(logPrefix + "Found " + Integer.toString(totalPlayers) + " player accounts");
				// get old players permissions
				st = conn.prepareStatement("SELECT `id`,`canBuy`,`canSell`,`isAdmin` FROM `"+dbPrefix+"Players`");
				rs = st.executeQuery();
				String tempPerms = "";
				while (rs.next()) {
					stNew = conn.prepareStatement("UPDATE `"+dbPrefix+"Players` SET `Permissions` = ? WHERE `id` = ?");
					tempPerms = "";
					if (rs.getBoolean("canBuy"))  tempPerms = this.addStringSet(tempPerms, "canBuy",  ",");
					if (rs.getBoolean("canSell")) tempPerms = this.addStringSet(tempPerms, "canSell", ",");
					if (rs.getBoolean("isAdmin")) tempPerms = this.addStringSet(tempPerms, "isAdmin", ",");
					stNew.setString(1, tempPerms);
					stNew.setInt   (2, rs.getInt("id"));
					stNew.executeUpdate();
					countPlayers++;
					WebAuctionPlus.PrintProgress(countPlayers, totalPlayers);
				}
				log.info(logPrefix + "Converted " + Integer.toString(countPlayers) + " player accounts");
			} catch (SQLException e) {
				log.warning(logPrefix + "Unable to update Players table!");
				e.printStackTrace();
			} finally {
				closeResources(conn, st, rs);
			}
		}
		// update items table
		if (debugSQL) log.info("WA Query: Convert Database Items");
		executeRawSQL("UPDATE `"+dbPrefix+"Items` SET `ItemTable`='Items' WHERE `ItemTable`=NULL");
		// move mail to items table
		if (tableExists("Mail")) {
			int countMail = 0;
			int totalMail = 0;
			conn = getConnection();
			st    = null;
			stNew = null;
			rs    = null;
			rs2   = null;
			try {
				if (debugSQL) log.info("WA Query: Convert Database Mail");
				// get total mail
				st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"Mail`");
				rs = st.executeQuery();
				if (!rs.next()) {
					log.severe(logPrefix + "Could not get total mail!");
					return;
				}
				totalMail = rs.getInt(1);
				log.info(logPrefix + "Found " + Integer.toString(totalMail) + " mail stacks");
				// get old mail items
				st = conn.prepareStatement("SELECT `name`,`damage`,`player`,`quantity` FROM `"+dbPrefix+"Mail`");
				rs = st.executeQuery();
				while (rs.next()) {
					stNew = conn.prepareStatement("INSERT INTO `"+dbPrefix+"Items` " +
						"(`ItemTable`,`itemId`,`itemDamage`,`playerName`,`qty`) VALUES ('Mail',?,?,?,?)");
					stNew.setInt   (1, rs.getInt   ("name"));
					stNew.setInt   (2, rs.getInt   ("damage"));
					stNew.setString(3, rs.getString("player"));
					stNew.setInt   (4, rs.getInt   ("quantity"));
					stNew.executeUpdate();
					countMail++;
					WebAuctionPlus.PrintProgress(countMail, totalMail);
				}
				log.info(logPrefix + "Converted " + Integer.toString(countMail) + " mail stacks");
			} catch (SQLException e) {
				log.warning(logPrefix + "Unable to update Mail table");
				e.printStackTrace();
			} finally {
				closeResources(conn, st, rs);
			}
		}
		// move data to new ItemEnchantments table
		if (tableExists("EnchantLinks")) {
			int countEnchantments = 0;
			int totalEnchantments = 0;
			conn = getConnection();
			st    = null;
			stNew = null;
			rs    = null;
			rs2   = null;
			try {
				if (debugSQL) log.info("WA Query: Convert Database Enchantments");
				// get total enchantments
				st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"EnchantLinks`");
				rs = st.executeQuery();
				if (!rs.next()) {
					log.severe(logPrefix + "Could not get total enchantments!");
					return;
				}
				totalEnchantments = rs.getInt(1);
				log.info(logPrefix + "Found " + Integer.toString(totalEnchantments) + " enchantments");
				// get old enchantments table
				st = conn.prepareStatement("SELECT `enchId`,`itemTableId`,`itemId` " +
					"FROM `"+dbPrefix+"EnchantLinks` ORDER BY `itemId` ASC");
				rs = st.executeQuery();
				while (rs.next()) {
					stNew = conn.prepareStatement("INSERT INTO `"+dbPrefix+"ItemEnchantments` (" +
						"`ItemTable`, `ItemTableId`, `enchName`, `enchId`, `level`) VALUES (" +
						"?, ?, ?, ?, ? )");
					// ItemTable Enum
					if        (rs.getInt("itemTableId") == 0) {
						stNew.setString(1, "Items");
					} else if (rs.getInt("itemTableId") == 1) {
						stNew.setString(1, "Auctions");
					} else if (rs.getInt("itemTableId") == 2) {
						stNew.setString(1, "Mail");
					} else {
						stNew.setString(1, "Items");
					}
					// ItemTableId
					stNew.setInt(2, rs.getInt("itemId"));
					// query Enchantments (old table)
					st = conn.prepareStatement("SELECT `enchName`, `enchId`, `level` " +
						"FROM `"+dbPrefix+"Enchantments` WHERE `id` = ?");
					st.setInt(1, rs.getInt("enchId"));
					rs2 = st.executeQuery();
					if (rs2.next()) {
						// enchName
						stNew.setString(3, rs2.getString("enchName"));
						// enchId
						stNew.setInt   (4, rs2.getInt   ("enchId"));
						// level
						stNew.setInt   (5, rs2.getInt   ("level"));
					} else {
						stNew.setString(3, null);
						stNew.setInt   (4, 0);
						stNew.setInt   (5, 0);
					}
					stNew.executeUpdate();
					countEnchantments++;
					WebAuctionPlus.PrintProgress(countEnchantments, totalEnchantments);
				}
				log.info(logPrefix + "Converted " + Integer.toString(countEnchantments) + " enchantments");
			} catch (SQLException e) {
				log.warning(logPrefix + "Unable to update Enchantments table");
				e.printStackTrace();
			} finally {
				closeResources(conn, st, rs);
			}
		}
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
				"FROM `"+dbPrefix+"Mail` WHERE `ItemTable` = 'Mail' AND `playerName` = ? AND `id` > ? ORDER BY `id` ASC LIMIT 1");
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
			if (debugSQL) log.info("WA Query: deleteMail " + player + " " + delMail.toString());
			String sql  = "";
			String sql2 = "";
			int i = 0;
			for (int mailId : delMail) {
				i++; if (i!=1) {
					sql  += " OR ";
					sql2 += " OR ";
				}
				sql  += "`id`="          + Integer.toString(mailId);
				sql2 += "`ItemTableId`=" + Integer.toString(mailId);
			}
			if (sql.isEmpty() || sql2.isEmpty()) return;
			st = conn.prepareStatement("DELETE FROM `"+dbPrefix+"Mail` " +
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
			if (debugSQL) log.info("WA Query: getItemEnchantments - Items Table" + Integer.toString(itemId));
			st = conn.prepareStatement("SELECT `enchName`, `enchId`, `level` FROM `"+dbPrefix+"ItemEnchantments` " +
				"WHERE `ItemTable` = 'Mail' AND `ItemTableId` = ? ORDER BY `enchId` DESC");
			st.setInt   (1, itemId);
			rs = st.executeQuery();
			while (rs.next()) {
				log.info(rs.getString("enchName") + " " + rs.getInt("level"));
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