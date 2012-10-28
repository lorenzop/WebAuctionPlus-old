package me.lorenzop.webauctionplus.mysql;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;
import java.util.logging.Logger;

public class MySQLPool {

	private String dbHost;
	private int    dbPort;
	private String dbUser;
	private String dbPass;
	private String dbName;
	private String dbPrefix;
	protected int ConnPoolSize_Warn	= 5;
	protected int ConnPoolSize_Hard	= 10;

	protected List<MySQLPoolConn> pool = new ArrayList<MySQLPoolConn> (1);

	protected final Logger log;
	protected final String logPrefix;


	// init pool
	public MySQLPool(Logger log, String logPrefix, String dbHost, int dbPort, String dbUser, String dbPass, String dbName, String dbPrefix) {
		this.log = log;
		this.logPrefix = logPrefix;
		// connection settings
		this.dbHost = dbHost;
		this.dbPort = dbPort;
		this.dbUser = dbUser;
		this.dbPass = dbPass;
		this.dbName = dbName;
		this.dbPrefix = dbPrefix;
	}


	// get a lock from pool
	public MySQLPoolConn getLock() {
		// find an available connection
		synchronized(pool) {
			for(MySQLPoolConn poolConn : pool) {
				if(poolConn.getLock()) return poolConn;
			}
			// check max pool size
			if(pool.size() >= ConnPoolSize_Hard) {
				log.severe(logPrefix+"DB connection pool is full! Hard limit reached!  Size: "+Integer.toString(pool.size()));
				return null;
			}
			if(pool.size() >= ConnPoolSize_Warn)
				log.warning(logPrefix+"DB connection pool is full! Warning limit reached.  Size: "+Integer.toString(pool.size()));
			// make a new connection
			MySQLPoolConn poolConn = new MySQLPoolConn(this, dbHost, dbPort, dbUser, dbPass, dbName, dbPrefix);
			pool.add(poolConn);
			poolConn.getLock();
			return poolConn;
		}
	}


	// close all connections
	public void forceCloseConnections() {
		for(int i=0; i < pool.size(); i++) {
			MySQLPoolConn poolConn = pool.get(i);
			poolConn.forceClose();
			poolConn = null;
			pool.remove(i);
		}
		int count = countInUse();
		if(count == 0) log.info(  logPrefix+"Successfully closed all MySQL connections.");
		else           log.severe(logPrefix+"Failed to close "+Integer.toString(count)+" MySQL connections!");
	}


	public void executeRawSQL(String sql) {
		MySQLPoolConn poolConn = getLock();
		Statement st = null;
		try {
			st = poolConn.getConn().createStatement();
			st.executeUpdate(sql);
		} catch (SQLException e) {
			log.warning(logPrefix+"Exception executing raw SQL: "+sql);
			e.printStackTrace();
		} finally {
			poolConn.releaseLock(st);
			poolConn = null;
		}
	}


	// utility functions
	protected boolean tableExists(String tableName) {
		boolean exists = false;
		MySQLPoolConn poolConn = getLock();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			st = poolConn.getConn().prepareStatement("SHOW TABLES LIKE ?");
			st.setString(1, dbPrefix+tableName);
			rs = st.executeQuery();
			while (rs.next())
				exists = true;
		} catch (SQLException e) {
			log.warning(logPrefix+"Unable to check if table exists: "+tableName);
			e.printStackTrace();
			return false;
		} finally {
			poolConn.releaseLock(st, rs);
			poolConn = null;
		}
		return exists;
	}
	protected boolean setTableExists(String tableName, String Sql) {
		if(tableExists(tableName)) return false;
		log.info(logPrefix+"Creating table "+tableName);
		executeRawSQL("CREATE TABLE `"+dbPrefix+tableName+"` ( "+Sql+" );");
		return true;
	}
	protected boolean columnExists(String tableName, String columnName) {
		boolean exists = false;
		MySQLPoolConn poolConn = getLock();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			st = poolConn.getConn().prepareStatement("SHOW COLUMNS FROM `"+dbPrefix+tableName+"` LIKE ?");
			st.setString(1, columnName);
			rs = st.executeQuery();
			while(rs.next()) {
				exists = true;
				break;
			}
		} catch(SQLException e) {
			log.warning(logPrefix+"Unable to check if table column exists: "+dbPrefix+tableName+"::"+columnName);
		} finally {
			poolConn.releaseLock(st, rs);
			poolConn = null;
		}
		return exists;
	}
	protected boolean setColumnExists(String tableName, String columnName, String Attr) {
		if(columnExists(tableName, columnName)) return false;
		log.info("Adding column "+columnName+" to table "+dbPrefix+tableName);
		executeRawSQL("ALTER TABLE `"+dbPrefix+tableName+"` ADD `"+columnName+"` "+Attr);
		return true;
	}


	public String dbPrefix() {
		return dbPrefix;
	}


	// set pool size
	public void setConnPoolSize_Warn(int size) {
		ConnPoolSize_Warn = size;
	}
	public void setConnPoolSize_Hard(int size) {
		ConnPoolSize_Hard = size;
	}
	public int countInUse() {
		int count = 0;
		for(MySQLPoolConn poolConn : pool)
			if(poolConn.inUse) count++;
		return count;
	}


}
