# session_store 开启和关闭状态下性能影响的统计

session_store 相当于开启了ssl_session_cache, 用于缓存https的握手记录，
并用于未来数据交换时复用，由于https握手需要做大量的CPU计算，该配置项可以节省cpu时间。

session_store 使用redis缓存握手记录，调用时间上的差异与redis本身和所处的内网环境息息相关, 
在内网环境相对良好且redis状态良好的情况下，开启状态可能会提升HTTPS握手阶段的速度。

以下测试给出的是360内部网络状态下，分别开启 session_store 和关闭 session_store 时，一个简单的HTTPS调用的时间差异。


### 开启
```bash
[root /]# ab -n 100 -c 20 <my_https_domain>

......

Server Software:        openresty
Server Hostname:        my_https_domain
Server Port:            443
SSL/TLS Protocol:       TLSv1.2,ECDHE-RSA-AES256-GCM-SHA384,2048,256
TLS Server Name:        my_https_domain

Document Path:          /
Document Length:        3 bytes

Concurrency Level:      20
Time taken for tests:   3.801 seconds
Complete requests:      100
Failed requests:        0
Total transferred:      22900 bytes
HTML transferred:       300 bytes
Requests per second:    26.31 [#/sec] (mean)
Time per request:       760.203 [ms] (mean)
Time per request:       38.010 [ms] (mean, across all concurrent requests)
Transfer rate:          5.88 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:      395  589 156.6    715     761
Processing:     9  168 154.6     32     365
Waiting:        8  166 154.0     31     363
Total:        737  757  11.2    757     788

Percentage of the requests served within a certain time (ms)
  50%    757
  66%    759
  75%    764
  80%    766
  90%    773
  95%    780
  98%    786
  99%    788
 100%    788 (longest request)
```

### 关闭

```bash
[root /]# ab -n 100 -c 20 <my_https_domain>

......

Server Software:        openresty
Server Hostname:        my_https_domain
Server Port:            443
SSL/TLS Protocol:       TLSv1.2,ECDHE-RSA-AES256-GCM-SHA384,2048,256
TLS Server Name:        my_https_domain

Document Path:          /
Document Length:        3 bytes

Concurrency Level:      20
Time taken for tests:   4.703 seconds
Complete requests:      100
Failed requests:        0
Total transferred:      22900 bytes
HTML transferred:       300 bytes
Requests per second:    21.27 [#/sec] (mean)
Time per request:       940.505 [ms] (mean)
Time per request:       47.025 [ms] (mean, across all concurrent requests)
Transfer rate:          4.76 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:      371  621 164.3    614     828
Processing:     8  190 162.6    277     492
Waiting:        7  179 157.7    195     394
Total:        705  812  71.7    801    1309

Percentage of the requests served within a certain time (ms)
  50%    801
  66%    819
  75%    824
  80%    829
  90%    834
  95%    842
  98%   1123
  99%   1309
 100%   1309 (longest request)
```

## 结论

根据上述测试指标，可以明显看出在开启session_store后，Requests_per_second，Time_per_request等指标都要好于关闭状态。