package me.lorenzop.webauctionplus.mysql;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.HashMap;

import me.lorenzop.webauctionplus.WebAuctionPlus;

public class MySQLTables {

	protected final String dbPrefix;
	protected final boolean debugSQL;
	protected final DataQueries dataQueries;

	protected final WebAuctionPlus plugin;
	private boolean isOk;

	public MySQLTables(WebAuctionPlus plugin) {
		this.plugin = plugin;
		this.dataQueries = WebAuctionPlus.dataQueries;
		this.dbPrefix = dataQueries.dbPrefix;
		this.debugSQL = dataQueries.debugSQL;
		isOk = false;

		// create new tables
		sqlTables("Auctions");
		sqlTables("Items");
		sqlTables("MarketPrices");
		sqlTables("Players");
		sqlTables("RecentSigns");
		sqlTables("SaleAlerts");
		sqlTables("SellPrice");
		sqlTables("ShoutSigns");
		sqlTables("Settings");

		// update existing tables from original web auction
		if (!tableExists("ItemEnchantments") && tableExists("EnchantLinks")) {
			sqlTables("ItemEnchantments");
			// convert database tables to Plus
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "**************************************");
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "*** Converting database to Plus... ***");
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "**************************************");
			ConvertDatabase10();
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Finished converting database to Plus!");
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "*** You can delete these tables from the database: EnchantLinks, Enchantments, Mail ***");
		} else
			sqlTables("ItemEnchantments");

		// update 1.0 to 1.1
		if(setColumnExists("Players",	"Locked",		"tinyint(1)   DEFAULT '0'") ) {
			setColumnExists("Items",	"enchantments",	"varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT ''");
			setColumnExists("Auctions",	"enchantments",	"varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT ''");
			ConvertDatabase11();
		}

		isOk = true;
	}
	public boolean isOk() {return this.isOk;}

	// table queries
	private void sqlTables(String tableName) {
		sqlTables(false, tableName);
	}
	private void sqlTables(boolean alter, String tableName) {
		if(alter)
			if (debugSQL) WebAuctionPlus.log.info("WA Query: sqlTables " + (alter?"Alter":"Create") + " " + tableName);
		// auctions
		if (tableName.equals("Auctions"))
			if (alter) {
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`player`		`playerName`	VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`name`			`itemId`		INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`damage`		`itemDamage`	INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`quantity`		`qty`			INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`price`			`price`			DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`created`		`created`		DATETIME		NOT NULL	DEFAULT '0000-00-00 00:00:00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`allowBids`		`allowBids`		TINYINT(1)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`currentBid`	`currentBid`	DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Auctions`	CHANGE		`currentWinner` `currentWinner`	VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
			} else
				setTableExists("Auctions",
					"`id`				INT    (11)		NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`playerName`		VARCHAR(16)		NULL		DEFAULT NULL	, " +
					"`itemId`			INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`itemDamage`		INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`qty`				INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`enchantments`		VARCHAR(255)	NOT NULL	DEFAULT ''		, " +
					"`price`			DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'	, " +
					"`created`			DATETIME		NOT NULL	DEFAULT '0000-00-00 00:00:00', " +
					"`allowBids`		TINYINT(1)		NOT NULL	DEFAULT '0'		, " +
					"`currentBid`		DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'	, " +
					"`currentWinner`	VARCHAR(16)		NULL		DEFAULT NULL	");
//		// ItemEnchantments
//		else if (tableName.equals("ItemEnchantments"))
//			if (alter)
//				WebAuctionPlus.log.severe("Shouldn't run this!");
//			else
//				setTableExists("ItemEnchantments",
//					"`id`				INT    (11)		NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
//					"`ItemTable`		ENUM   ('Items','Auctions','Mail') NULL DEFAULT NULL, " +
//					"`ItemTableId`		INT    (11)		NOT NULL	DEFAULT '0'		, " +
//					"`enchName`			VARCHAR(32)		NULL		DEFAULT NULL	, " +
//					"`enchId`			INT    (11)		NOT NULL	DEFAULT '0'		, " +
//					"`level`			TINYINT(3)		UNSIGNED NOT NULL DEFAULT '0' ");
		// Items
		else if (tableName.equals("Items"))
			if (alter) {
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`player`		`playerName`	VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`name`			`itemId`		INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`damage`		`itemDamage`	INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Items`		CHANGE		`quantity`		`qty`			INT    (11)		NOT NULL	DEFAULT '0'");
			} else
				setTableExists("Items",
					"`id`				INT    (11)		NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`playerName`		VARCHAR(16)		NULL		DEFAULT NULL	, " +
					"`itemId`			INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`itemDamage`		INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`qty`				INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`enchantments`		VARCHAR(255)	NOT NULL	DEFAULT ''		");
		// MarketPrices
		else if (tableName.equals("MarketPrices"))
			if (alter) {
				executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`name`			`itemId`		INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`damage`		`itemDamage`	INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`time`			`time`			DATETIME		NOT NULL	DEFAULT '0000-00-00 00:00:00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`marketprice`	`marketprice`	DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"MarketPrices` CHANGE	`ref`			`ref`			INT    (11)		NOT NULL	DEFAULT '0'");
			} else
				setTableExists("MarketPrices",
					"`id`				INT    (11)		NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`itemId`			INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`itemDamage`		INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`time`				DATETIME		NOT NULL	DEFAULT '0000-00-00 00:00:00', " +
					"`marketprice`		DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'	, " +
					"`ref`				INT    (11)		NOT NULL	DEFAULT '0'		");
		// Players
		else if (tableName.equals("Players"))
			if (alter) {
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`name`			`playerName`	VARCHAR(16)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`pass`			`password`		VARCHAR(32)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	CHANGE		`money`			`money`			DOUBLE (11,2) 	NOT NULL	DEFAULT '0.00'");
				executeRawSQL("ALTER IGNORE TABLE `"+dbPrefix+"Players`	CHANGE		`itemsSold`		`itemsSold`		INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER IGNORE TABLE `"+dbPrefix+"Players`	CHANGE		`itemsBought`	`itemsBought`	INT    (11)		NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER IGNORE TABLE `"+dbPrefix+"Players`	CHANGE		`earnt`			`earnt`			DOUBLE (11,2) 	NOT NULL	DEFAULT '0.00'");
				executeRawSQL("ALTER IGNORE TABLE `"+dbPrefix+"Players`	CHANGE		`spent`			`spent`			DOUBLE (11,2) 	NOT NULL	DEFAULT '0.00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"Players`	ADD			`Permissions`	SET( 'canBuy', 'canSell', 'isAdmin' )	NULL	DEFAULT NULL");
			} else
				setTableExists("Players",
					"`id`				INT    (11)		NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`playerName`		VARCHAR(16)		NULL		DEFAULT NULL	, " +
					"`password`			VARCHAR(32)		NULL		DEFAULT NULL	, " +
					"`money`			DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'	, " +
					"`itemsSold`		INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`itemsBought`		INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`earnt`			DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'	, " +
					"`spent`			DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'	, " +
					"`Permissions`		SET( 'canBuy', 'canSell', 'isAdmin' ) NULL DEFAULT NULL ," +
					"`Locked`			TINYINT(1)		NOT NULL	DEFAULT '0'");
		// RecentSigns
		else if (tableName.equals("RecentSigns"))
			if (alter) {
				executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`world`			`world`		VARCHAR(32)			CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`offset`		`offset`	INT    (11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`x`				`x`			INT    (11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`y`				`y`			INT    (11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"RecentSigns` CHANGE		`z`				`z`			INT    (11)			NOT NULL	DEFAULT '0'");
			} else
				setTableExists("RecentSigns",
					"`id`				INT    (11)		NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`world`			VARCHAR(32)		NULL		DEFAULT NULL	, " +
					"`offset`			INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`x`				INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`y`				INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`z`				INT    (11)		NOT NULL	DEFAULT '0'		");
		// SaleAlerts
		else if (tableName.equals("SaleAlerts"))
			if (alter) {
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`seller`		`seller`	VARCHAR(16)			CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`quantity`		`qty`		INT    (11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`price`			`price`		DOUBLE (11,2)		NOT	NULL	DEFAULT '0.00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`buyer`			`buyer`		VARCHAR(16)			CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`item`			`item`		VARCHAR(16)			CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SaleAlerts`	CHANGE		`alerted`		`alerted`	TINYINT(1)			NOT NULL	DEFAULT '0'");
			} else
				setTableExists("SaleAlerts",
					"`id`				INT    (11)		NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`seller`			VARCHAR(16)		NULL		DEFAULT NULL	, " +
					"`qty`				INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`price`			DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'	, " +
					"`buyer`			VARCHAR(16)		NULL		DEFAULT NULL	, " +
					"`item`				VARCHAR(16)		NULL		DEFAULT NULL	, " +
					"`alerted`			TINYINT(1)		NOT NULL	DEFAULT '0'		");
		// SellPrice
		else if (tableName.equals("SellPrice"))
			if (alter) {
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`name`			`itemId`	INT    (11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`damage`		`itemDamage` INT   (11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`time`			`time`		DATETIME			NOT NULL	DEFAULT '0000-00-00 00:00:00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`quantity`		`qty`		INT    (11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`price`			`price`		DOUBLE (11,2)		NOT NULL	DEFAULT '0.00'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`seller`		`seller`	VARCHAR(16)			CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"SellPrice`	CHANGE		`buyer`			`buyer`		VARCHAR(16)			CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
			} else
				setTableExists("SellPrice",
					"`id`				INT    (11)		NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`itemId`			INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`itemDamage`		INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`time`				DATETIME		NOT NULL	DEFAULT '0000-00-00 00:00:00', " +
					"`qty`				INT    (11)		NOT NULL	DEFAULT '0'		, " +
					"`price`			DOUBLE (11,2)	NOT NULL	DEFAULT '0.00'	, " +
					"`seller`			VARCHAR(16)		NULL		DEFAULT NULL	, " +
					"`buyer`			VARCHAR(16)		NULL		DEFAULT NULL	");
		// Settings
		else if (tableName.equals("Settings"))
			if (alter)
				WebAuctionPlus.log.severe("Shouldn't run this!");
			else
				setTableExists("Settings",
					"`id`				INT(11)			NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`name`				VARCHAR(32)		NULL		DEFAULT NULL	, UNIQUE(`name`)   , " +
					"`value`			VARCHAR(255)	NULL		DEFAULT NULL	");
		// ShoutSigns
		else if (tableName.equals("ShoutSigns"))
			if (alter) {
				executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`world`		`world`		VARCHAR(32)		CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`radius`	`radius`	INT(11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`x`			`x`			INT(11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`y`			`y`			INT(11)			NOT NULL	DEFAULT '0'");
				executeRawSQL("ALTER TABLE `"+dbPrefix+"ShoutSigns`	CHANGE		`z`			`z`			INT(11)			NOT NULL	DEFAULT '0'");
			} else
				setTableExists("ShoutSigns",
					"`id`				INT(11)			NOT NULL	AUTO_INCREMENT	, PRIMARY KEY(`id`), " +
					"`world`			VARCHAR(32)		NULL		DEFAULT NULL	, " +
					"`radius`			INT(11)			NOT NULL	DEFAULT '0'		, " +
					"`x`				INT(11)			NOT NULL	DEFAULT '0'		, " +
					"`y`				INT(11)			NOT NULL	DEFAULT '0'		, " +
					"`z`				INT(11)			NOT NULL	DEFAULT '0'		");
	}

	// convert database tables to Plus
	private void ConvertDatabase10() {
		// update tables
		sqlTables(true, "Auctions");
		sqlTables(true, "Items");
		sqlTables(true, "MarketPrices");
		sqlTables(true, "Players");
		sqlTables(true, "RecentSigns");
		sqlTables(true, "SaleAlerts");
		sqlTables(true, "SellPrice");
		sqlTables(true, "ShoutSigns");

		Connection conn			= null;
		PreparedStatement st	= null;
		PreparedStatement stNew	= null;
		ResultSet rs			= null;
		ResultSet rs2			= null;

		// check if already updated
		try {
			conn  = getConnection();
			if (debugSQL) WebAuctionPlus.log.info("WA Query: Count Database Settings");
			st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"Settings`");
			rs = st.executeQuery();
			if (!rs.next()) {
				WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix + "Could not get total settings!");
				return;
			}
			if (rs.getInt(1) != 0) {
				WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix + "ERROR!! ALREADY CONVERTED DATABASE!!");
				return;
			}
			// add a setting
			if (debugSQL) WebAuctionPlus.log.info("WA Query: Insert Version Setting");
			st = conn.prepareStatement("INSERT INTO `"+dbPrefix+"Settings` (`name`,`value`) VALUES ('Version',?)");
			st.setString(1, plugin.getDescription().getVersion().toString());
			st.executeUpdate();
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to update Players table!");
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
			closeResources(stNew, rs2);
		}

		// update player permissions
		if (tableExists("Players")) {
			int countPlayers = 0;
			int totalPlayers = 0;
			conn  = getConnection();
			st    = null;
			stNew = null;
			rs    = null;
			rs2   = null;
			try {
				if (debugSQL) WebAuctionPlus.log.info("WA Query: Convert Database Players");
				// get total players
				st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"Players`");
				rs = st.executeQuery();
				if (!rs.next()) {
					WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix + "Could not get total players!");
					return;
				}
				totalPlayers = rs.getInt(1);
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Found " + Integer.toString(totalPlayers) + " player accounts");
				// get old players permissions
				st = conn.prepareStatement("SELECT `id`,`canBuy`,`canSell`,`isAdmin` FROM `"+dbPrefix+"Players`");
				rs = st.executeQuery();
				String tempPerms = "";
				while (rs.next()) {
					stNew = conn.prepareStatement("UPDATE `"+dbPrefix+"Players` SET `Permissions` = ? WHERE `id` = ?");
					tempPerms = "";
					if (rs.getBoolean("canBuy"))  tempPerms = WebAuctionPlus.addStringSet(tempPerms, "canBuy",  ",");
					if (rs.getBoolean("canSell")) tempPerms = WebAuctionPlus.addStringSet(tempPerms, "canSell", ",");
					if (rs.getBoolean("isAdmin")) tempPerms = WebAuctionPlus.addStringSet(tempPerms, "isAdmin", ",");
					stNew.setString(1, tempPerms);
					stNew.setInt   (2, rs.getInt("id"));
					stNew.executeUpdate();
					countPlayers++;
					if(totalPlayers > 500) WebAuctionPlus.PrintProgress(countPlayers, totalPlayers);
				}
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Converted " + Integer.toString(countPlayers) + " player accounts");
			} catch (SQLException e) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to update Players table!");
				e.printStackTrace();
			} finally {
				closeResources(conn, st, rs);
				closeResources(stNew, rs2);
			}
		}
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
				if (debugSQL) WebAuctionPlus.log.info("WA Query: Convert Database Mail");
				// get total mail
				st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"Mail`");
				rs = st.executeQuery();
				if (!rs.next()) {
					WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix + "Could not get total mail!");
					return;
				}
				totalMail = rs.getInt(1);
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Found " + Integer.toString(totalMail) + " mail stacks");
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
					if(totalMail > 500) WebAuctionPlus.PrintProgress(countMail, totalMail);
				}
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Converted " + Integer.toString(countMail) + " mail stacks");
			} catch (SQLException e) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to update Mail table");
				e.printStackTrace();
			} finally {
				closeResources(conn, st, rs);
				closeResources(stNew, rs2);
			}
			// make sure nothing's null
			executeRawSQL("UPDATE `"+dbPrefix+"Items` SET `ItemTable`='Items' WHERE `ItemTable` IS NULL");
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
				if (debugSQL) WebAuctionPlus.log.info("WA Query: Convert Database Enchantments");
				// get total enchantments
				st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"EnchantLinks`");
				rs = st.executeQuery();
				if (!rs.next()) {
					WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix + "Could not get total enchantments!");
					return;
				}
				totalEnchantments = rs.getInt(1);
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Found " + Integer.toString(totalEnchantments) + " enchantments");
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
					if(totalEnchantments > 500) WebAuctionPlus.PrintProgress(countEnchantments, totalEnchantments);
				}
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Converted " + Integer.toString(countEnchantments) + " enchantments");
			} catch (SQLException e) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to update Enchantments table");
				e.printStackTrace();
			} finally {
				closeResources(conn, st, rs);
				closeResources(stNew, rs2);
			}
		}
	}


	// update from 1.0 to 1.1
	private void ConvertDatabase11() {
		Connection conn			= null;
		PreparedStatement st	= null;
		PreparedStatement stNew	= null;
		ResultSet rs			= null;
		ResultSet rs2			= null;

		if(tableExists("ItemEnchantments")) {
			int totalItemEnchantments = 0;
			int countEnchantments = 0;
			conn = getConnection();
			st    = null;
			stNew = null;
			rs    = null;
			rs2   = null;
			try {
				if (debugSQL) WebAuctionPlus.log.info("WA Query: Convert ItemEnchantments");
				// get total enchantments
				st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+dbPrefix+"ItemEnchantments`");
				rs = st.executeQuery();
				if (!rs.next()) {
					WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix + "Could not get total item enchantments!");
					return;
				}
				totalItemEnchantments = rs.getInt(1);
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Found " + Integer.toString(totalItemEnchantments) + " item enchantments");
				// get old item enchantments
				st = conn.prepareStatement("SELECT `ItemTable`, `ItemTableId`, `enchId`, `level`  FROM `"+dbPrefix+"ItemEnchantments`");
				rs = st.executeQuery();
				HashMap<Integer, String> enchTempAuctions = new HashMap<Integer, String>();
				HashMap<Integer, String> enchTempItems    = new HashMap<Integer, String>();
				while (rs.next()) {
					// auctions table
					if(rs.getString("ItemTable").equals("Auctions")) {
						// add enchantment string
						String enchStr = "";
						if(enchTempAuctions.containsKey( rs.getInt("ItemTableId") )) enchStr = enchTempAuctions.get( rs.getInt("ItemTableId") )+",";
						enchStr += Integer.toString(rs.getInt("enchId"))+":"+Integer.toString(rs.getInt("level"));
						enchTempAuctions.put(rs.getInt("ItemTableId"), enchStr);
						// update row
						stNew = conn.prepareStatement("UPDATE `"+dbPrefix+"Auctions` SET `enchantments` = ? WHERE `id` = ? LIMIT 1");
						stNew.setString(1, enchStr);
						stNew.setInt   (2, rs.getInt("ItemTableId"));
					// items table
					} else {
						// add enchantment string
						String enchStr = "";
						if(enchTempItems.containsKey( rs.getInt("ItemTableId") )) enchStr = enchTempItems.get( rs.getInt("ItemTableId") )+",";
						enchStr += Integer.toString(rs.getInt("enchId"))+":"+Integer.toString(rs.getInt("level"));
						enchTempItems.put(rs.getInt("ItemTableId"), enchStr);
						// update row
						stNew = conn.prepareStatement("UPDATE `"+dbPrefix+"Items` SET `enchantments` = ? WHERE `id` = ? LIMIT 1");
						stNew.setString(1, enchStr);
						stNew.setInt   (2, rs.getInt("ItemTableId"));
					}
					stNew.executeUpdate();
					countEnchantments++;
					if(totalItemEnchantments > 500) WebAuctionPlus.PrintProgress(countEnchantments, totalItemEnchantments);
				}
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Converted " + Integer.toString(countEnchantments) + " enchantments for "+
					Integer.toString(enchTempAuctions.size()+enchTempItems.size())+" items");
			} catch (SQLException e) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to convert enchantments");
				e.printStackTrace();
			} finally {
				closeResources(conn, st, rs);
				closeResources(stNew, rs2);
			}
		}

	}


	public Connection getConnection() {
		return dataQueries.getConnection();
	}
	public void closeResources(Connection conn, Statement st, ResultSet rs) {
		dataQueries.closeResources(conn, st, rs);
	}
	public void closeResources(Connection conn) {
		dataQueries.closeResources(conn);
	}
	public void closeResources(Statement st, ResultSet rs) {
		dataQueries.closeResources(st, rs);
	}
	public void executeRawSQL(String sql) {
		dataQueries.executeRawSQL(sql);
	}
	protected boolean tableExists(String tableName) {
		return dataQueries.tableExists(tableName);
	}
	protected boolean setTableExists(String tableName, String Sql) {
		return dataQueries.setTableExists(tableName, Sql);
	}
	protected boolean columnExists(String tableName, String columnName) {
		return dataQueries.columnExists(tableName, columnName);
	}
	protected boolean setColumnExists(String tableName, String columnName, String Attr) {
		return dataQueries.setColumnExists(tableName, columnName, Attr);
	}


}