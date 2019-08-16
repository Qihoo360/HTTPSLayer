# 使用接入层和不使用接入层的性能对比
* ::cpu::  40核  Intel(R) Xeon(R) CPU E5-2630 v4 @ 2.20GHz
* ::内存:: 64G
* ::内核版本:: Linux version 3.10.0-957.10.1.el7.x86_64
* ::网卡:: 千兆网卡
* ::nginx work process::  1
* ::openresty版本:: 1.15.8.1

#### 测试命令
```shell
ab -n 2000 -c 50 domain
```
#### HTTP请求

##### CPU使用率
![](images/14D2D3B9-83EF-4CBE-A55A-D3487C037178.png)

##### 测试结果
```bash
Concurrency Level:      50
Time taken for tests:   4.783 seconds
Complete requests:      2000
Failed requests:        0
Total transferred:      458000 bytes
HTML transferred:       6000 bytes
Requests per second:    418.13 [#/sec] (mean)
Time per request:       119.581 [ms] (mean)
Time per request:       2.392 [ms] (mean, across all concurrent requests)
Transfer rate:          93.51 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:       16   51  70.4     28     472
Processing:    17   53  72.8     30     538
Waiting:       16   53  72.7     30     538
Total:         42  104 100.6     61     563
```


#### 静态的加载证书（不使用接入层）
##### CPU使用率
![](images/2DE6631E-E522-4118-9D4E-C35316BEE915.png)

##### 测试结果
```bash
Concurrency Level:      50
Time taken for tests:   21.977 seconds
Complete requests:      2000
Failed requests:        0
Total transferred:      458000 bytes
HTML transferred:       6000 bytes
Requests per second:    91.01 [#/sec] (mean)
Time per request:       549.419 [ms] (mean)
Time per request:       10.988 [ms] (mean, across all concurrent requests)
Transfer rate:          20.35 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:      117  436 160.0    429    2054
Processing:    15   93  82.1     60     647
Waiting:       15   84  74.4     56     519
Total:        313  529 157.6    497    2137
```


#### 动态加载证书（使用接入层）

##### CPU使用率
![](images/5D899A72-910B-429B-9444-E358AA27FB1B.png)

##### 测试结果
```bash
Concurrency Level:      50
Time taken for tests:   22.023 seconds
Complete requests:      2000
Failed requests:        0
Total transferred:      458000 bytes
HTML transferred:       6000 bytes
Requests per second:    90.81 [#/sec] (mean)
Time per request:       550.586 [ms] (mean)
Time per request:       11.012 [ms] (mean, across all concurrent requests)
Transfer rate:          20.31 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:       97  426 215.0    418    2704
Processing:    14  103  92.9     48     541
Waiting:       14   92  80.8     46     402
Total:        253  528 200.4    480    2961
```

#### 结论
* 不使用接入层每个https的请求的平均处理时间是10.988毫秒，使用接入层之后每个https的平均处理时间为11.012毫秒。因此，使用接入层并不会增加请求的处理时间，但是会增加cpu的使用率，大概在30%左右
