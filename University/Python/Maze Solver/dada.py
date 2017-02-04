neighbours = [1,2,3,4]
count = 0
while count != 3:
    hold = neighbours[0]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[0] = hold2
    print neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    print neighbours
    
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    print neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    print neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[1]
    neighbours[1] = hold
    neighbours[2] = hold2
    print neighbours
    
    hold = neighbours[2]
    hold2 = neighbours[3]
    neighbours[3] = hold
    neighbours[2] = hold2
    print neighbours
    count +=1

hold = neighbours[0]
hold2 = neighbours[3]
neighbours[3] = hold
neighbours[0] = hold2
print neighbours

hold = neighbours[2]
hold2 = neighbours[3]
neighbours[3] = hold
neighbours[2] = hold2
print neighbours


hold = neighbours[2]
hold2 = neighbours[1]
neighbours[1] = hold
neighbours[2] = hold2
print neighbours

hold = neighbours[2]
hold2 = neighbours[3]
neighbours[3] = hold
neighbours[2] = hold2
print neighbours

hold = neighbours[2]
hold2 = neighbours[1]
neighbours[1] = hold
neighbours[2] = hold2
print neighbours

hold = neighbours[2]
hold2 = neighbours[3]
neighbours[3] = hold
neighbours[2] = hold2
print neighbours