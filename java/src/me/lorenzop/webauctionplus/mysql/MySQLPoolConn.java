package me.lorenzop.webauctionplus.mysql;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class MySQLPoolConn {

	protected Boolean inUse = false;
	protected boolean failed = false;
	protected Connection conn = null;
	protected final MySQLPool pool;

	private final String dbHost;
	private final int    dbPort;
	private final String dbUser;
	private final String dbPass;
	private final String dbName;
	private final String dbPrefix;


	public MySQLPoolConn(MySQLPool pool, String dbHost, int dbPort, String dbUser, String dbPass, String dbName, String dbPrefix) {
		this.pool   = pool;
		this.dbHost = dbHost;
		this.dbPort = dbPort;
		this.dbUser = dbUser;
		this.dbPass = dbPass;
		this.dbName = dbName;
		this.dbPrefix = dbPrefix;
	}


	// get a lock on this connection
	public boolean getLock() {
		if(failed) return(true);
		// get inUse lock
		synchronized(inUse) {
			if(inUse) return false;
			inUse = true;
		}
		// make connection
		Connect();
		return true;
	}
	public Connection getConn() {
		if(failed) return(null);
		Connect();
		if(failed || conn == null) {
			pool.log.severe(pool.logPrefix+"Failed to get a MySQL connection!");
			return(null);
		}
		return conn;
	}
	// make connection
	public void Connect() {
		if(failed) return;
		// already connected
		try {
			if(conn != null)
				if(!conn.isClosed())
					return;
		} catch(SQLException ignore) {}
		try {
			pool.log.info(pool.logPrefix+"Making a new MySQL connection..");
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			conn = DriverManager.getConnection("jdbc:mysql://"+dbHost+":"+Integer.toString(dbPort)+"/"+dbName, dbUser, dbPass);
			if(conn != null)
				if(!conn.isClosed()) return;
		} catch (ClassNotFoundException e) {
			pool.log.severe(pool.logPrefix+"Unable to load database driver!");
			e.printStackTrace();
		} catch (InstantiationException e) {
			pool.log.severe(pool.logPrefix+"Unable to create database driver!");
			e.printStackTrace();
		} catch (IllegalAccessException e) {
			pool.log.severe(pool.logPrefix+"Unable to create database driver!");
			e.printStackTrace();
		} catch (SQLException e) {
			pool.log.severe(pool.logPrefix+"SQL Error!");
			e.printStackTrace();
		}
		pool.log.severe(pool.logPrefix+"There was a problem getting the MySQL connection!!!");
		conn = null;
		failed = true;
	}


	// release lock
	public void releaseLock() {
		inUse = false;
	}
	public void releaseLock(Statement st, ResultSet rs) {
		freeResource(st, rs);
		releaseLock();
	}
	public void releaseLock(Statement st) {
		freeResource(st, null);
		releaseLock();
	}
	public void freeResource(Statement st, ResultSet rs) {
		if(st != null) {
			try {
				st.close();
				st = null;
			} catch (SQLException ignore) {}
		}
		if(rs != null) {
			try {
				rs.close();
				rs = null;
			} catch (SQLException ignore) {}
		}
	}
	public void freeResource(Statement st) {
		freeResource(st, null);
	}


	// resources
	public String dbPrefix() {
		return dbPrefix;
	}
	public void forceClose() {
		if(conn == null) return;
		try {
			conn.close();
		} catch (SQLException ignore) {}
	}


}
