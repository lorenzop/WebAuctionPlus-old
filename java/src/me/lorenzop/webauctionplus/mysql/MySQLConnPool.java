package me.lorenzop.webauctionplus.mysql;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;
import java.util.logging.Logger;

import me.lorenzop.webauctionplus.WebAuctionPlus;

public class MySQLConnPool {

	protected String dbHost;
	protected int    dbPort;
	protected String dbUser;
	protected String dbPass;
	protected String dbName;
	protected String dbPrefix;
	protected int ConnPoolSizeWarn	= 5;
	protected int ConnPoolSizeHard	= 10;

	private List<Boolean> inuse = new ArrayList<Boolean> (4);
	private List<Connection> connections = new ArrayList<Connection> (4);

	protected static Logger log = Logger.getLogger("Minecraft");
	protected static String logPrefix = "";


	// get a db connection from pool
	public Connection getConnection() {
		// find an available connection
		synchronized (inuse) {
			for(int i = 0; i != inuse.size(); i++) {
				if(!inuse.get(i)) {
					inuse.set(i, true);
					try {
						if(connections.get(i).isValid(2) == false) {
							inuse.remove(i);
							connections.remove(i);
							break;
						}
					} catch (SQLException e) {
						e.printStackTrace();
					}
					return connections.get(i);
				}
			}
		}
		// check max pool size
		if(connections.size() >= ConnPoolSizeHard) {
			log.severe(logPrefix+"DB connection pool is full! Hard limit reached!  Size:"+Integer.toString(connections.size()));
			return null;
		} else if(connections.size() >= ConnPoolSizeWarn) {
			log.warning(logPrefix+"DB connection pool is full! Warning limit reached.  Size: "+Integer.toString(connections.size()));
		}
		// make a new connection
		try {
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			Connection conn = DriverManager.getConnection("jdbc:mysql://"+dbHost+":"+Integer.toString(dbPort)+"/"+dbName, dbUser, dbPass);
			connections.add(conn);
			inuse.add(true);
			return conn;
		} catch (ClassNotFoundException e) {
			WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix+"Unable to load database driver!");
			e.printStackTrace();
		} catch (InstantiationException e) {
			WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix+"Unable to create database driver!");
			e.printStackTrace();
		} catch (IllegalAccessException e) {
			WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix+"Unable to create database driver!");
			e.printStackTrace();
		} catch (SQLException e) {
			WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix+"SQL Error!");
			e.printStackTrace();
		}
		log.severe(logPrefix+"Exception getting MySQL Connection");
		return null;
	}


	// set connection pool size
	public void setConnPoolSizeWarn(int size) {
		ConnPoolSizeWarn = size;
	}
	public void setConnPoolSizeHard(int size) {
		ConnPoolSizeHard = size;
	}


	// close resources
	public void closeResources(Connection conn, Statement st, ResultSet rs) {
		closeResources(conn);
		closeResources(st, rs);
	}
	public void closeResources(Connection conn, Statement st) {
		closeResources(conn);
		closeResources(st, null);
	}
	public void closeResources(Connection conn) {
		if (conn == null) return;
		boolean valid = false;
		try {
			valid = conn.isValid(1);
		} catch (SQLException e) {
			e.printStackTrace();
		}
		synchronized(inuse) {
			int i = connections.indexOf(conn);
			if (valid) {
				inuse.set(i, false);
			} else {
				inuse.remove(i);
				connections.remove(i);
			}
		}
	}
	public void closeResources(Statement st, ResultSet rs) {
		if (rs != null) {
			try {
				rs.close();
				rs = null;
			} catch (SQLException ignore) {}
		}
		if (st != null) {
			try {
				st.close();
				st = null;
			} catch (SQLException ignore) {}
		}
	}
	public void forceCloseConnections() {
		try {
			for(int i=0; i<inuse.size(); i++)
				connections.get(i).close();
		} catch (SQLException ignore) {}
	}


	public void executeRawSQL(String sql) {
		Connection conn = getConnection();
		Statement st = null;
		ResultSet rs = null;
		try {
			st = conn.createStatement();
			st.executeUpdate(sql);
		} catch (SQLException e) {
			log.warning(logPrefix+"Exception executing raw SQL: "+sql);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}


// TODO: cache the tables list so it only needs to load once
	protected boolean tableExists(String tableName) {
		boolean exists = false;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			st = conn.prepareStatement("SHOW TABLES LIKE ?");
			st.setString(1, dbPrefix+tableName);
			rs = st.executeQuery();
			while (rs.next())
				exists = true;
		} catch (SQLException e) {
			log.warning(logPrefix+"Unable to check if table exists: "+tableName);
			e.printStackTrace();
			return false;
		} finally {
			closeResources(conn, st, rs);
		}
		return exists;
	}
	protected boolean setTableExists(String tableName, String Sql) {
		if (tableExists(tableName)) return false;
		log.info(logPrefix+"Creating table "+tableName);
		executeRawSQL("CREATE TABLE `"+dbPrefix+tableName+"` ( "+Sql+" );");
		return true;
	}


	protected boolean columnExists(String tableName, String columnName) {
		boolean exists = false;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			st = conn.prepareStatement("SHOW COLUMNS FROM `"+dbPrefix+tableName+"` LIKE ?");
			st.setString(1, columnName);
			rs = st.executeQuery();
			while (rs.next()) {
				exists = true;
				break;
			}
		} catch (SQLException e) {
			log.warning(logPrefix+"Unable to check if table column exists: "+dbPrefix+tableName+"::"+columnName);
		}
		return exists;
	}
	protected boolean setColumnExists(String tableName, String columnName, String Attr) {
		if (columnExists(tableName, columnName)) {return false;}
		log.info("Adding column "+columnName+" to table "+dbPrefix+tableName);
		executeRawSQL("ALTER TABLE `"+dbPrefix+tableName+"` ADD `"+columnName+"` "+Attr);
		return true;
	}


	// access layer
	public boolean isDebug() {
		return WebAuctionPlus.isDebug();
	}
	public String dbPrefix() {
		return dbPrefix;
	}


}
