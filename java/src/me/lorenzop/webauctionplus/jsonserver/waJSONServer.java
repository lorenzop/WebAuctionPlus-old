package me.lorenzop.webauctionplus.jsonserver;

import java.io.IOException;
import me.lorenzop.webauctionplus.WebAuctionPlus;

public class waJSONServer extends Thread {

	public final String host;
	public final int port;
//	public final ServerSocket listener;

@SuppressWarnings("unused")
	private final WebAuctionPlus plugin;

	public waJSONServer(WebAuctionPlus plugin, String host, int port) throws IOException {
		this.plugin = plugin;
		if(host==null || host.isEmpty()) host = "*";
		this.host = host;
		this.port = port;
//		InetSocketAddress address;
//		// Initialize the listener
//		if(host.equals("*") || host.equalsIgnoreCase("any")) {
//			// listen on all ip's
//			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Starting JSON server on *:" + Integer.toString(port));
//			address = new InetSocketAddress(port);
//		} else {
//			// listen on host
//			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Starting JSON server on " + host + ":" + Integer.toString(port));
//			address = new InetSocketAddress(host, port);
//		}
//		listener = new ServerSocket();
//		listener.bind(address);
	}

	@Override
	public void run() {
//		try {
//			while(true) {
//				// wait for and accept incoming connections
//				Socket socket = listener.accept();
//				// Create a new thread to handle the request.
//				(new Thread(new Request(plugin, socket))).start();
//			}
//		} catch (IOException e) {
//			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Error! Stopping JSON server");
//			e.printStackTrace();
//		}
	}

}
