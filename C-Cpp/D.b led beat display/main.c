#include "stm32f10x_gpio.h"
#include "stm32f10x_rcc.h"

void delay_ms(int delay);
void beat(int beat1, int beat2);
#define ONE_SECOND 1000;


int main()
{
	RCC_APB2PeriphClockCmd(RCC_APB2Periph_GPIOC, ENABLE);

	GPIO_InitTypeDef GPIO_InitStructure;

	GPIO_StructInit(&GPIO_InitStructure);
	GPIO_InitStructure.GPIO_Speed = GPIO_Speed_2MHz;
	GPIO_InitStructure.GPIO_Pin = GPIO_Pin_9;
	GPIO_InitStructure.GPIO_Mode = GPIO_Mode_Out_PP;
	GPIO_Init(GPIOC, &GPIO_InitStructure);

	GPIO_StructInit(&GPIO_InitStructure);
	GPIO_InitStructure.GPIO_Speed = GPIO_Speed_2MHz;
	GPIO_InitStructure.GPIO_Pin = GPIO_Pin_8;
	GPIO_InitStructure.GPIO_Mode = GPIO_Mode_Out_PP;
	GPIO_Init(GPIOC, &GPIO_InitStructure);


    while(1)
    {
    	beat(3,4);
    }
}

void delay_ms(register int count)
{
	const int MAX=3400;
	register int i, j;

	for (i=0; i<count; i++)
			for(j=0; j<MAX; j++)
	return;
}
void beat(int beat1, int beat2)
{
	register int count = 0;
	while (count < (beat1*beat2))
		{
		if ((count % beat1) == 0)
				{
			GPIO_WriteBit(GPIOC, GPIO_Pin_9, Bit_SET);
				}
		if ((count % beat1) != 0)
			{
			GPIO_WriteBit(GPIOC, GPIO_Pin_9, Bit_RESET);
			}

		if ((count % beat2) == 0)
				{
			GPIO_WriteBit(GPIOC, GPIO_Pin_8, Bit_SET);
				}
		if ((count % beat2) != 0)
			{
			GPIO_WriteBit(GPIOC, GPIO_Pin_8, Bit_RESET);
			}

		delay_ms(1000);
		count ++;
		}
	return;
}
