package me.lorenzop.webauctionplus.tasks;

import java.io.InputStream;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

import me.lorenzop.webauctionplus.WebAuctionPlus;

public class CronExecutorTask implements Runnable {

	// urls to query
	private List<String> cronUrls = new ArrayList<String>();

	public CronExecutorTask () {
	}

	public void setCronUrl(String url) {
		String[] urls = url.split(";");
		for (int i=0; i<urls.length; i++) {
			cronUrls.add(urls[i]);
		}
	}
	public void clearCronUrls() {
		cronUrls.clear();
	}

	public void run() {
		for (String url : cronUrls) {
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Running Cron Executor: " + url);
			if (!url.startsWith("http://") && !url.startsWith("https://")) {
					WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Invalid url!");
					continue;
			}
			WebAuctionPlus.log.info(executeUrl(url));
		}
	}

	private String executeUrl(String urlString) {
		try {
			URL url         = new URL(urlString);
			InputStream in  = url.openStream();
			StringBuffer sb = new StringBuffer();
			byte[] buffer   = new byte[256];
			int byteRead = 0;
			while(true) {
				byteRead = in.read(buffer);
				if (byteRead == -1) break;
				for (int i = 0; i<byteRead; i++)
					sb.append((char)buffer[i]);
			}
			return sb.toString();
		} catch (Exception e) {
			e.printStackTrace();
		}
		return null;
	}

}
