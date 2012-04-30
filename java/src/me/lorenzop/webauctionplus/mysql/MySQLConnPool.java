package me.lorenzop.webauctionplus;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;
import java.util.logging.Logger;

public class MySQLConnPool {

	protected String dbHost;
	protected String dbPort;
	protected String dbUser;
	protected String dbPass;
	protected String dbName;
	protected String dbPrefix = "";
	public int ConnPoolSizeWarn = 6;
	public int ConnPoolSizeHard = 20;

	private List<Boolean> inuse = new ArrayList<Boolean> (4);
	private List<Connection> connections = new ArrayList<Connection> (4);

	public static Logger log = Logger.getLogger("Minecraft");
	public static String logPrefix = "";

	public Connection getConnection() {
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

		if(connections.size() >= ConnPoolSizeHard) {
			log.severe(logPrefix + "DB connection pool is full! Hard limit reached!  Size:" + Integer.toString(connections.size()));
			return null;
		} else if(connections.size() >= ConnPoolSizeWarn) {
			log.warning(logPrefix + "DB connection pool is full! Warning limit reached.  Size: " + Integer.toString(connections.size()));
		}
		try {
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			Connection conn = DriverManager.getConnection("jdbc:mysql://"+dbHost+":"+dbPort+"/"+dbName, dbUser, dbPass);
			connections.add(conn);
			inuse.add(true);
			return conn;
		} catch (InstantiationException e) {
			e.printStackTrace();
		} catch (IllegalAccessException e) {
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		log.severe(logPrefix + "Exception getting mySQL Connection");
		return null;
	}

	public void releaseConnection(Connection conn) {
		boolean valid = false;
		try {
			valid = conn.isValid(1);
		} catch (SQLException e) {
			e.printStackTrace();
		}
		synchronized(inuse) {
			int i = connections.indexOf(conn);
			inuse.set(i, false);
			if(!valid) {
				inuse.remove(i);
				connections.remove(i);
			}
		}
	}

	public void closeResources(Connection conn, Statement st, ResultSet rs) {
		releaseConnection(conn);
		closeResources(st, rs);
	}
	public void closeResources(Statement st, ResultSet rs) {
		if (rs != null) {
			try {
				rs.close();
			} catch (SQLException e) {}
		}
		if (st != null) {
			try {
				st.close();
			} catch (SQLException e) {}
		}
	}

	public void forceCloseConnections() {
		for(int i = 0; i != inuse.size(); i++) {
			try {
				connections.get(i).close();
			} catch (SQLException e) {}
		}
	}

	public String addStringSet(String baseString, String addThis, String Delim) {
		if (addThis.isEmpty())    return baseString;
		if (baseString.isEmpty()) return addThis;
		return baseString + Delim + addThis;
	}

	public void executeRawSQL(String sql) {
		Connection conn = getConnection();
		Statement st = null;
		ResultSet rs = null;
		try {
			st = conn.createStatement();
			st.executeUpdate(sql);
		} catch (SQLException e) {
			log.warning(logPrefix + "Exception executing raw SQL: " + sql);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
	}

	protected boolean tableExists(String tableName) {
		boolean exists = false;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			st = conn.prepareStatement("SHOW TABLES LIKE ?");
			st.setString(1, dbPrefix + tableName);
			rs = st.executeQuery();
			while (rs.next()) {
				exists = true;
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to check if table exists: " + tableName);
			e.printStackTrace();
		} finally {
			closeResources(conn, st, rs);
		}
		return exists;
	}

	protected void setTableExists(String tableName, String Sql) {
		if (tableExists(tableName)) {return;}
		log.info(logPrefix + "Creating table " + tableName);
		executeRawSQL("CREATE TABLE `" + dbPrefix + tableName + "` ( "+Sql+" );");
	}

	protected boolean columnExists(String tableName, String columnName) {
		boolean exists = false;
		Connection conn = getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			st = conn.prepareStatement("SHOW COLUMNS FROM `" + dbPrefix + tableName + "` LIKE ?");
			st.setString(1, columnName);
			rs = st.executeQuery();
			while (rs.next()) {
				exists = true;
				break;
			}
		} catch (SQLException e) {
			log.warning(logPrefix + "Unable to check if table column exists: " + dbPrefix + tableName + "::" + columnName);
		}
		return exists;
	}

	protected void setColumnExists(String tableName, String columnName, String Attr) {
		if (columnExists(tableName, columnName)) {return;}
		log.info("Adding column " + columnName + " to table " + dbPrefix + tableName);
		executeRawSQL("ALTER TABLE `" + dbPrefix + tableName + "` ADD `" + columnName + "` " + Attr);
	}


}
