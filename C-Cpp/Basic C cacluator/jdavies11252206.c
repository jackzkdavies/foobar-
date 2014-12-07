#include <stdio.h>
calculate(int value,char shift, int opp);
int main(void)
{
    unsigned int value;
    unsigned int shift;
    char opp[4];

    printf("Type in an expression: ");
    scanf("%x %s %x",&value,&opp,&shift);
    printf("The answer is: ");

    printf("%x \n",calculate(value,shift,opp));

    return 0;
}

calculate(int value,char shift,int opp){
    if (strcmp(opp, "add") == 0){return value+shift;}
    if (strcmp(opp, "sub") == 0){return value-shift;}
    if (strcmp(opp, "and") == 0){return value&&shift;}
    if (strcmp(opp, "or") == 0){return value||shift;}
    if (strcmp(opp, "xor") == 0){return value^shift;}
    if (strcmp(opp, "and") == 0){return value&&shift;}
    if (strcmp(opp, "shl") == 0){return value<<shift;}
    if (strcmp(opp, "shr") == 0){return value>>shift;}
    if (strcmp(opp, "asr") == 0){return value >> shift | ~(~0U >> shift);}
    if (strcmp(opp, "rol") == 0){return (value << shift | value >>(64- shift));}
    if (strcmp(opp, "ror") == 0){return (value >> shift | value << (64- shift));}
}
