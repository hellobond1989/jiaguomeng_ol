# jiaguomeng_ol
家国梦建筑摆放计算-在线版

离线版：https://github.com/SQRPI/JiaGuoMeng 

离线版帖子：https://bbs.nga.cn/read.php?tid=18677204

在线不限流地址：http://other.qikor.com/jiaguomeng/

感谢NGA-根派以及其他各位提供数据和测试的同学

## 在线版说明

在线版是根据python离线版做的一个版本
因为计算组合的数据比较多，当一个人使用时问题还不明显，并发后会经常超时导致计算失败
所以在线版对特定建筑做了禁用处理，减少数据的计算量

现在把代码传上来，如果有愿意做线上分流的同学，可以在自己的服务器上搭建一下，感谢~
如果想在本地使用，那可以下载一个一键搭建windows PHP 环境的软件，配置好后把文件扔进去就好

最后祝各位国庆快乐~

## 文件说明

 - init.php 配置文件，所有的建筑加成系数、禁用的建筑、建筑的分类都在这里，文件最下面有家国之光的设置
 - index.html 前端展示页面，页面初始化后请求jiaguomeng.php文件
 - jiaguomeng.php 后端计算文件
