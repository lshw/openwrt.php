<?php
if (empty($db)) {
	class MyDB extends SQLite3
	{
		var  $db_name = '';
		var  $is_open = false;
		function __construct()
		{
		}
		public function fetch_one_assoc($sql)
		{
			return $this->querySingle($sql, true);
		}
		public function fetch_assoc($result)
		{
			return $result->fetchArray();
		}
		public function query($sql) {
			if($this->is_open == false) {
				$this->db_name = __DIR__."/hosts.sqlite3";
				echo '<br>'.$this->db_name;
				if(!file_exists($this->db_name)) {
					$this->open($this->db_name);
					$this->exec("CREATE TABLE IF NOT EXISTS hosts (
id INTEGER PRIMARY KEY  AUTOINCREMENT,
office int(11) comment '地点编号‘,
createtime TIMESTAMP comment ’建立日期',
host varchar(100) comment '地址',
port int(11) comment '端口',
x float comment 'x偏移，以门为原点，东为正',
y float comment 'y偏移，以门为原点，南为正',
user varchar(100) comment '登陆用户名',
passwd varchar(100) comment '登陆密码',
			token varchar(100) comment '登录临时token',
			login_time TIMESTAMP comment '登陆时间',
			hostname varchar(100) comment '名称',
			model varchar(20) comment '型号',
			ver varchar(100) comment 'rom版本',
			wan varchar(20) comment 'wan口设备',
			lan1 varchar(20) comment 'lan1口设备',
			lan2 varchar(20) comment 'lan2口设备',
			lan3 varchar(20) comment 'lan3口设备',
			lan4 varchar(20) comment 'lan4口设备',
					)");
		$this->exec("create INDEX IF NOT EXISTS office on hosts(office);");
		$this->close();
		chmod($this->db_name, 0666);
				} //db_name not exists
				$this->open($this->db_name);
				$this->busyTimeout(50000);
				$this->exec("PRAGMA journal_mode=wal");
		}//is_open
		} //query
	}
		$db = new MyDB();
}

