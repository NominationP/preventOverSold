# Prevent Over Sale


## Mysql

1. code running sort
```
    // mysql innodb ==> update ==> row lock ( Exclusive lock )

    $sql = "update store set amount = amount-1 where goods_id = 12345";
    $this->db_instance->query($sql);
    $sql = "select amount from store where goods_id = 12345";
    $count = $this->db_instance->query($sql)->fetch_assoc()['amount'
```

2. all atomization 

```
    // atomization with select && condition
    
    $sql = "update store set amount = amount-1 where goods_id = 12345 and amount >= 1";
    $re = $this->db_instance->query($sql);
```


## Redis
