!/bin/bash
declare -a ARR

mapfile -t ARR < <(aws ec2 run-instances --image-id $1 --count $2 --instance-type $3 --security-group-id $4 --subnet-id $5 --key-name $6 --iam-instance-profile $7 --associate-public-ip-address --user-data install-webserver.sh) 

#ec2 wait command-
aws ec2 wait instance-running --instance-ids ${ARR[@]}

#load balancer creation
aws elb create-load-balancer --load-balancer-name ITMO-544-MP-loadbalancer --listeners "Protocol=HTTP,LoadBalancerPort=80,InstanceProtocol=HTTP,InstancePort=80" --security-group-id $4 --subnet-id $5 

#load balancer registration
aws elb register-instances-with-load-balancer --load-balancer-name ITMO-544-MP-loadbalancer --instance-ids ${ARR[@]} 

#health check policy configuration
aws elb configure-health-check --load-balancer-name ITMO-544-MP-loadbalancer --health-check Target=HTTP:80/png,Interval=30,UnhealthyThreshold=2,HealthyThreshold=2,Timeout=3

#cookie-stickiness policy configuration
aws elb create-lb-cookie-stickiness-policy --load-balancer-name ITMO-544-MP-loadbalancer --policy-name ITMO-544-cookiepolicy --cookie-expiration-period 60

#launch configuration creattion
aws autoscaling create-launch-configuration --launch-configuration-name itmo544-launch-config --image-id $1 --count $2 --instance-type $3 --security-groups $4  --key-name $6  --user-data install-webserver.sh --iam-instance-profile $7

#Autoscaling group creation
aws autoscaling create-auto-scaling-group --auto-scaling-group-name itmo-544-autoscaling --launch-configuration-name itmo544-launch-config --load-balancer-names ITMO-544-MP-loadbalancer  --health-check-type ELB --min-size 3 --max-size 6 --desired-capacity 3 --default-cooldown 600 --health-check-grace-period 120 --vpc-zone-identifier $5 

#AutoScaling Policy-Increase

INCREASE=(`aws autoscaling put-scaling-policy --auto-scaling-group-name itmo-544-autoscaling --policy-name scalingpolicyincrease --scaling-adjustment 3 --adjustment-type ChangeInCapacity`)

#AutoScaling Policy-Decrease

DECREASE=(`aws autoscaling put-scaling-policy --auto-scaling-group-name itmo-544-autoscaling --policy-name scalingpolicydecrease --scaling-adjustment -3 --adjustment-type ChangeInCapacity`)

#Cloud Watch Metric 

aws cloudwatch put-metric-alarm --alarm-name Add --alarm-description "CPU exceeds 30 percent" --metric-name CPUUtilization --namespace AWS/EC2 --statistic Average --period 60 --threshold 30 --comparison-operator GreaterThanOrEqualToThreshold --evaluation-periods 1 --unit Percent --dimensions "Name=itmo-544-autoscaling" --alarm-actions $INCREASE

aws cloudwatch put-metric-alarm --alarm-name Reduce --alarm-description "CPU falls below 10 percent" --metric-name CPUUtilization --namespace AWS/EC2 --statistic Average --period 60 --threshold 10 --comparison-operator LessThanOrEqualToThreshold --evaluation-periods 1 --unit Percent --dimensions "Name=itmo-544-autoscaling" --alarm-actions $DECREASE

#SNS topic for image subscription

SNSTOPICPICARN=(`aws sns create-topic --name snspicture`)
aws sns set-topic-attributes --topic-arn $SNSTOPICPICARN --attribute-name Name --attribute-value snspicture  

#SNS topic for cloud watch subscription

SNSTOPICWATCHARN=(`aws sns create-topic --name snswatch`)
aws sns set-topic-attributes --topic-arn $SNSTOPICWATCHARN --attribute-name WatchName --attribute-value snswatch

#Subcribe

EMAILID=unln@hawk.iit.edu

aws sns subscribe --topic-arn $SNSTOPICWATCHARN --protocol email --notification-endpoint $EMAILID

#database subnet creation-
aws rds create-db-subnet-group --db-subnet-group-name ITMO544DBSubnet --subnet-ids subnet-b2b1e999 subnet-42351f1b --db-subnet-group-description 544subnet

#AWS RDS instances creation
aws rds create-db-instance mp1 --engine MySQL --db-name Project --db-instance-class db.t2.micro --engine MySQL --allocated-storage 5 --master-username UzmaFarheen --master-user-password UzmaFarheen --db-subnet-group-name ITMO544DBSubnet

#read replica creation
aws rds-create-db-instance-read-replica mp1-replica --source-db-instance-identifier-value mp1
