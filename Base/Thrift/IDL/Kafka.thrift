namespace php Services.Kafka
service Kafka
{
    bool producer(1:string topic, 2:string func , 3:map<string, string> data);
}